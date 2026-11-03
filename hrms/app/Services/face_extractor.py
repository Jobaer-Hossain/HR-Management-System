"""Server-side face detection and recognition using SCRFD + ArcFace ONNX models."""

import base64
import json
import os
import sys
from pathlib import Path

import cv2
import numpy as np
import onnxruntime as ort

FACE_SIZE = (112, 112)
DET_SIZE = 640
DET_STRIDES = (8, 16, 32)
DET_THRESHOLD = 0.60
NMS_THRESHOLD = 0.40


def error(message):
    return {"success": False, "error": message}


def load_frames(input_path):
    raw = Path(input_path).read_text(encoding="utf-8")
    try:
        payload = json.loads(raw)
        frames = payload.get("frames", []) if isinstance(payload, dict) else payload
    except json.JSONDecodeError:
        frames = [raw]
    if not isinstance(frames, list):
        return []
    return [frame for frame in frames if isinstance(frame, str) and frame.strip()]


def decode_frame(data_url):
    encoded = data_url.split(",", 1)[1] if "," in data_url else data_url
    try:
        image = cv2.imdecode(np.frombuffer(base64.b64decode(encoded), np.uint8), cv2.IMREAD_COLOR)
    except Exception:
        image = None
    if image is None or image.size == 0:
        raise ValueError("Invalid camera image.")
    return image


def anchor_centers(height, width, stride, anchors=2):
    grid_x, grid_y = np.meshgrid(np.arange(width), np.arange(height))
    centers = np.stack((grid_x, grid_y), axis=-1).reshape(-1, 2).astype(np.float32)
    centers = centers * stride
    return np.repeat(centers, anchors, axis=0)


class ScrfdDetector:
    def __init__(self, model_path):
        self.session = ort.InferenceSession(str(model_path), providers=["CPUExecutionProvider"])
        self.input_name = self.session.get_inputs()[0].name

    def detect(self, image):
        original_h, original_w = image.shape[:2]
        scale = min(DET_SIZE / original_w, DET_SIZE / original_h)
        resized_w = max(1, int(round(original_w * scale)))
        resized_h = max(1, int(round(original_h * scale)))
        resized = cv2.resize(image, (resized_w, resized_h), interpolation=cv2.INTER_LINEAR)
        canvas = np.zeros((DET_SIZE, DET_SIZE, 3), dtype=np.uint8)
        canvas[:resized_h, :resized_w] = resized

        blob = cv2.dnn.blobFromImage(
            canvas, 1.0 / 128.0, (DET_SIZE, DET_SIZE), (127.5, 127.5, 127.5), swapRB=True
        )
        outputs = self.session.run(None, {self.input_name: blob})

        boxes, scores, landmarks = [], [], []
        for level, stride in enumerate(DET_STRIDES):
            score_map = outputs[level].reshape(-1)
            bbox_map = outputs[3 + level].reshape(-1, 4) * stride
            kps_map = outputs[6 + level].reshape(-1, 5, 2) * stride
            feat_h = DET_SIZE // stride
            feat_w = DET_SIZE // stride
            centers = anchor_centers(feat_h, feat_w, stride)
            keep = np.where(score_map >= DET_THRESHOLD)[0]
            for idx in keep:
                cx, cy = centers[idx]
                left, top, right, bottom = bbox_map[idx]
                boxes.append([cx - left, cy - top, cx + right, cy + bottom])
                scores.append(float(score_map[idx]))
                landmarks.append(centers[idx] + kps_map[idx])

        if not boxes:
            return None

        nms_boxes = [[int(x), int(y), int(max(0, x2 - x)), int(max(0, y2 - y))]
                     for x, y, x2, y2 in boxes]
        selected = cv2.dnn.NMSBoxes(nms_boxes, scores, DET_THRESHOLD, NMS_THRESHOLD)
        if len(selected) == 0:
            return None
        selected = np.asarray(selected).reshape(-1)

        # More than one person may be visible in a real camera frame. Select
        # the clearest usable face instead of failing the whole scan. SCRFD's
        # confidence alone is not enough: a larger, sharper face is normally
        # the person standing in front of the terminal.
        candidates = []
        for index in selected:
            candidate_index = int(index)
            box = np.asarray(boxes[candidate_index], dtype=np.float32) / scale
            points = np.asarray(landmarks[candidate_index], dtype=np.float32) / scale
            box[0::2] = np.clip(box[0::2], 0, original_w - 1)
            box[1::2] = np.clip(box[1::2], 0, original_h - 1)
            x1, y1, x2, y2 = box.astype(int)
            crop = image[max(0, y1):max(y1 + 1, y2), max(0, x1):max(x1 + 1, x2)]
            if crop.size == 0:
                continue
            gray_crop = cv2.cvtColor(crop, cv2.COLOR_BGR2GRAY)
            sharpness = float(cv2.Laplacian(gray_crop, cv2.CV_64F).var())
            area_ratio = max(0.0, float((x2 - x1) * (y2 - y1)) / float(original_w * original_h))
            clarity = min(1.0, sharpness / 250.0)
            size_score = min(1.0, area_ratio / 0.18)
            selection_score = (
                float(scores[candidate_index]) * 0.50
                + clarity * 0.35
                + size_score * 0.15
            )
            candidates.append({
                "box": box,
                "landmarks": points,
                "score": float(scores[candidate_index]),
                "sharpness": sharpness,
                "selection_score": selection_score,
            })

        if not candidates:
            return None
        best = max(candidates, key=lambda candidate: candidate["selection_score"])
        best["faces_detected"] = len(candidates)
        return best


class ArcFaceEmbedder:
    def __init__(self, model_path):
        self.session = ort.InferenceSession(str(model_path), providers=["CPUExecutionProvider"])
        self.input_name = self.session.get_inputs()[0].name
        self.output_name = self.session.get_outputs()[0].name

    def embed(self, image, detection):
        template = np.array([
            [38.2946, 51.6963], [73.5318, 51.5014], [56.0252, 71.7366],
            [41.5493, 92.3655], [70.7299, 92.2041]
        ], dtype=np.float32)
        transform, _ = cv2.estimateAffinePartial2D(
            detection["landmarks"].astype(np.float32), template, method=cv2.LMEDS
        )
        if transform is None:
            raise ValueError("Could not align the detected face.")
        aligned = cv2.warpAffine(image, transform, FACE_SIZE, borderValue=0.0)
        blob = cv2.dnn.blobFromImage(
            aligned, 1.0 / 127.5, FACE_SIZE, (127.5, 127.5, 127.5), swapRB=True
        )
        vector = self.session.run([self.output_name], {self.input_name: blob})[0][0].astype(np.float32)
        norm = np.linalg.norm(vector)
        if norm == 0:
            raise ValueError("Empty facial embedding returned by the model.")
        return vector / norm


def analyze(frames, model_dir, detector=None, embedder=None, fast=False):
    if len(frames) < 3:
        return error("At least three camera frames are required for biometric verification.")

    detector = detector or ScrfdDetector(model_dir / "det_10g.onnx")
    embedder = embedder or ArcFaceEmbedder(model_dir / "w600k_r50.onnx")

    if fast:
        images = [decode_frame(data_url) for data_url in frames[:5]]
        gray_frames = [cv2.resize(cv2.cvtColor(image, cv2.COLOR_BGR2GRAY), (96, 96)) for image in images]
        image = images[len(images) // 2]
        detection = detector.detect(image)
        if not detection:
            return error("No clear human face was detected in the attendance frame.")
        if detection["score"] < DET_THRESHOLD:
            return error("No clear human face was detected in the attendance frame.")
        x1, y1, x2, y2 = detection["box"].astype(int)
        face_crop = image[max(0, y1):max(y1 + 1, y2), max(0, x1):max(x1 + 1, x2)]
        sharpness = float(cv2.Laplacian(cv2.cvtColor(face_crop, cv2.COLOR_BGR2GRAY), cv2.CV_64F).var())
        if sharpness < 35.0:
            return error("The face is too blurry. Hold the camera steady and try again.")
        motion = max(float(np.mean(cv2.absdiff(gray_frames[i - 1], gray_frames[i]))) for i in range(1, len(gray_frames)))
        if motion < 0.20:
            return error("Live capture was not confirmed. Please move your head slightly and retry.")
        vector = embedder.embed(image, detection)
        return {
            "success": True,
            "engine": "scrfd-arcface-onnx-fast",
            "dimension": 512,
            "descriptor": [round(float(value), 7) for value in vector],
            "quality": round(float(min(1.0, detection["score"] * 0.7 + min(1.0, sharpness / 250.0) * 0.3)), 3),
            "consistency": 1.0,
            "liveness": {"passed": True, "frames": len(images), "motion": round(motion, 3)},
        }

    embeddings, quality_scores, gray_frames = [], [], []

    for data_url in frames[:5]:
        image = decode_frame(data_url)
        detection = detector.detect(image)
        if not detection:
            return error("No clear human face was detected in one of the camera frames.")
        if detection["score"] < DET_THRESHOLD:
            return error("No clear human face was detected in one of the camera frames.")

        x1, y1, x2, y2 = detection["box"].astype(int)
        face_crop = image[max(0, y1):max(y1 + 1, y2), max(0, x1):max(x1 + 1, x2)]
        if face_crop.size == 0:
            return error("The detected face area is invalid.")
        sharpness = float(cv2.Laplacian(cv2.cvtColor(face_crop, cv2.COLOR_BGR2GRAY), cv2.CV_64F).var())
        if sharpness < 35.0:
            return error("The face is too blurry. Hold the camera steady and try again.")

        embeddings.append(embedder.embed(image, detection))
        quality_scores.append(min(1.0, detection["score"] * 0.7 + min(1.0, sharpness / 250.0) * 0.3))
        gray_frames.append(cv2.resize(cv2.cvtColor(image, cv2.COLOR_BGR2GRAY), (96, 96)))

    stack = np.stack(embeddings)
    mean_vector = stack.mean(axis=0)
    mean_vector /= np.linalg.norm(mean_vector)
    consistency = float(np.mean(np.dot(stack, mean_vector)))
    motion = max(float(np.mean(cv2.absdiff(gray_frames[i - 1], gray_frames[i]))) for i in range(1, len(gray_frames)))
    if consistency < 0.82:
        return error("The face moved too much between frames. Keep your face inside the guide and retry.")
    if motion < 0.20:
        return error("Live capture was not confirmed. Please move your head slightly and retry.")

    return {
        "success": True,
        "engine": "scrfd-arcface-onnx",
        "dimension": 512,
        "descriptor": [round(float(value), 7) for value in mean_vector],
        "quality": round(float(np.mean(quality_scores)), 3),
        "consistency": round(consistency, 3),
        "liveness": {"passed": True, "frames": len(frames[:5]), "motion": round(motion, 3)},
    }


def main():
    if len(sys.argv) < 2:
        print(json.dumps(error("No biometric input supplied.")))
        return
    model_dir = Path(sys.argv[2] if len(sys.argv) > 2 else os.environ.get("FACE_MODEL_DIR", "storage/app/face-models"))
    required = [model_dir / "det_10g.onnx", model_dir / "w600k_r50.onnx"]
    if any(not path.exists() for path in required):
        print(json.dumps(error("Face recognition models are not installed.")))
        return
    try:
        print(json.dumps(analyze(load_frames(sys.argv[1]), model_dir)))
    except Exception as exc:
        print(json.dumps(error(str(exc))))


if __name__ == "__main__":
    main()
