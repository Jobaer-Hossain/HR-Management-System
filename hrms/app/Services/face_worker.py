"""Local-only persistent biometric worker. Models are loaded once per process."""

import json
import sys
from http.server import BaseHTTPRequestHandler, ThreadingHTTPServer
from pathlib import Path

from face_extractor import ArcFaceEmbedder, ScrfdDetector, analyze, error


class Worker(BaseHTTPRequestHandler):
    detector = None
    embedder = None
    model_dir = None

    def do_GET(self):
        if self.path == "/health":
            self.send_json({"success": True, "engine": "scrfd-arcface-onnx", "ready": True})
        else:
            self.send_error(404)

    def do_POST(self):
        if self.path != "/extract":
            self.send_error(404)
            return
        try:
            length = int(self.headers.get("Content-Length", "0"))
            if length > 25_000_000:
                self.send_json(error("Biometric request is too large."), 413)
                return
            payload = json.loads(self.rfile.read(length).decode("utf-8"))
            result = analyze(
                payload.get("frames", []),
                self.model_dir,
                self.detector,
                self.embedder,
                bool(payload.get("fast", False)),
            )
            self.send_json(result, 200 if result.get("success") else 422)
        except Exception as exc:
            self.send_json(error(str(exc)), 422)

    def log_message(self, *_args):
        return

    def send_json(self, payload, status=200):
        body = json.dumps(payload).encode("utf-8")
        self.send_response(status)
        self.send_header("Content-Type", "application/json")
        self.send_header("Content-Length", str(len(body)))
        self.end_headers()
        self.wfile.write(body)


def main():
    host = sys.argv[1] if len(sys.argv) > 1 else "127.0.0.1"
    port = int(sys.argv[2]) if len(sys.argv) > 2 else 8765
    model_dir = Path(sys.argv[3] if len(sys.argv) > 3 else "storage/app/face-models")
    Worker.model_dir = model_dir
    Worker.detector = ScrfdDetector(model_dir / "det_10g.onnx")
    Worker.embedder = ArcFaceEmbedder(model_dir / "w600k_r50.onnx")
    ThreadingHTTPServer((host, port), Worker).serve_forever()


if __name__ == "__main__":
    main()
