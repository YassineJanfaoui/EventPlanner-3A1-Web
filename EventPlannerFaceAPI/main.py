from fastapi import FastAPI, UploadFile, File
import face_recognition
import numpy as np
from io import BytesIO
from PIL import Image
import os

app = FastAPI()

# Load known faces from storage (you can also connect to DB later)
known_faces = {}
KNOWN_DIR = "./known_faces"
for filename in os.listdir(KNOWN_DIR):
    img_path = os.path.join(KNOWN_DIR, filename)
    image = face_recognition.load_image_file(img_path)
    encoding = face_recognition.face_encodings(image)[0]
    username = filename.split('.')[0]
    known_faces[username] = encoding

@app.post("/recognize/")
async def recognize(file: UploadFile = File(...)):
    contents = await file.read()
    img = np.array(Image.open(BytesIO(contents)))
    
    unknown_encoding = face_recognition.face_encodings(img)
    if not unknown_encoding:
        return {"success": False, "message": "No face found."}

    unknown_encoding = unknown_encoding[0]

    for username, encoding in known_faces.items():
        match = face_recognition.compare_faces([encoding], unknown_encoding, tolerance=0.5)[0]
        if match:
            return {"success": True, "username": username}
    
    return {"success": False, "message": "Face not recognized"}
