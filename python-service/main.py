"""
Max AI - AI Tools microservice
-----------------------------------------
Microservice kecil berbasis FastAPI yang dipanggil oleh Laravel lewat HTTP untuk:
1. /remove-bg      - hapus background foto (rembg, model U2Net)
2. /enhance-image  - perbesar & pertajam gambar (Pillow: Lanczos upscale + Unsharp Mask)

Cara jalankan:
    cd python-service
    python3 -m venv venv
    source venv/bin/activate        # Windows: venv\\Scripts\\activate
    pip install -r requirements.txt
    uvicorn main:app --host 127.0.0.1 --port 8001

Model U2Net akan otomatis di-download oleh rembg saat pertama kali dipakai
(butuh koneksi internet sekali di awal, lalu di-cache lokal).
"""

from fastapi import FastAPI, File, UploadFile, HTTPException, Form
from fastapi.responses import Response
from rembg import remove, new_session
from PIL import Image, ImageFilter
import io

app = FastAPI(title="Max AI - AI Tools Service")

# Load model U2Net sekali saat service start (biar request pertama tidak lambat)
session = new_session("u2net")

ALLOWED_CONTENT_TYPES = {"image/jpeg", "image/png", "image/webp"}
MAX_ENHANCE_DIMENSION = 4000  # batasi supaya tidak habiskan memori server


@app.get("/health")
def health():
    return {"status": "ok", "model": "u2net"}


@app.post("/remove-bg")
async def remove_background(file: UploadFile = File(...)):
    if file.content_type not in ALLOWED_CONTENT_TYPES:
        raise HTTPException(status_code=422, detail="Format file tidak didukung.")

    input_bytes = await file.read()

    if not input_bytes:
        raise HTTPException(status_code=422, detail="File kosong.")

    try:
        output_bytes = remove(input_bytes, session=session)
    except Exception as exc:  # noqa: BLE001
        raise HTTPException(status_code=500, detail=f"Gagal memproses gambar: {exc}")

    return Response(content=output_bytes, media_type="image/png")


@app.post("/enhance-image")
async def enhance_image(file: UploadFile = File(...), scale: float = Form(2.0)):
    if file.content_type not in ALLOWED_CONTENT_TYPES:
        raise HTTPException(status_code=422, detail="Format file tidak didukung.")

    input_bytes = await file.read()

    if not input_bytes:
        raise HTTPException(status_code=422, detail="File kosong.")

    scale = max(1.0, min(scale, 4.0))  # batasi antara 1x - 4x

    try:
        image = Image.open(io.BytesIO(input_bytes)).convert("RGB")

        new_width = int(image.width * scale)
        new_height = int(image.height * scale)

        if max(new_width, new_height) > MAX_ENHANCE_DIMENSION:
            ratio = MAX_ENHANCE_DIMENSION / max(new_width, new_height)
            new_width = int(new_width * ratio)
            new_height = int(new_height * ratio)

        upscaled = image.resize((new_width, new_height), Image.LANCZOS)
        sharpened = upscaled.filter(ImageFilter.UnsharpMask(radius=2, percent=150, threshold=3))

        output = io.BytesIO()
        sharpened.save(output, format="PNG")
        output.seek(0)
    except Exception as exc:  # noqa: BLE001
        raise HTTPException(status_code=500, detail=f"Gagal memperbesar gambar: {exc}")

    return Response(content=output.getvalue(), media_type="image/png")

