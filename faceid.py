import face_recognition
import os
import sys
import json

def compare_faces(uploaded_image_path, faces_dir):
    try:
        # Load the uploaded image
        uploaded_image = face_recognition.load_image_file(uploaded_image_path)
        uploaded_encoding = face_recognition.face_encodings(uploaded_image)
        
        if not uploaded_encoding:
            return {"match": False, "error": "No face found in uploaded image"}
            
        uploaded_encoding = uploaded_encoding[0]
        
        # Iterate through all images in the faces directory
        for filename in os.listdir(faces_dir):
            if filename.lower().endswith(('.png', '.jpg', '.jpeg')):
                known_image_path = os.path.join(faces_dir, filename)
                known_image = face_recognition.load_image_file(known_image_path)
                known_encoding = face_recognition.face_encodings(known_image)
                
                if known_encoding:
                    # Compare faces
                    results = face_recognition.compare_faces([known_encoding[0]], uploaded_encoding)
                    if True in results:
                        return {"match": True, "user": filename.split('.')[0]}
        
        return {"match": False, "error": "No matching face found"}
    except Exception as e:
        return {"match": False, "error": str(e)}

if __name__ == "__main__":
    if len(sys.argv) < 3:
        print(json.dumps({"match": False, "error": "Missing arguments"}))
        sys.exit(1)
        
    uploaded_image = sys.argv[1]
    faces_dir = sys.argv[2]
    
    result = compare_faces(uploaded_image, faces_dir)
    print(json.dumps(result))