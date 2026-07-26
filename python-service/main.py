"""
Max AI - Remove Background microservice
-----------------------------------------
Microservice kecil berbasis FastAPI + rembg (model U2Net) yang dipanggil
oleh Laravel (RemoveBackgroundController) lewat HTTP untuk menghapus
background foto.

Cara jalankan:
    cd python-service
    python3 -m venv venv
    source venv/bin/activate        # Windows: venv\\Scripts\\activate
    pip install -r requirements.txt
    uvicorn main:app --host 127.0.0.1 --port 8001

Model U2Net akan otomatis di-download oleh rembg saat pertama kali dipakai
(butuh koneksi internet sekali di awal, lalu di-cache lokal).
"""

from fastapi import FastAPI, File, UploadFile, HTTPException
from fastapi.responses import Response
from rembg import remove, new_session
import io

app = FastAPI(title="Max AI - Remove Background Service")

# Load model U2Net sekali saat service start (biar request pertama tidak lambat)
session = new_session("u2net")

ALLOWED_CONTENT_TYPES = {"image/jpeg", "image/png", "image/webp"}


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
