import face_recognition
import sys
import json

def main():
    if len(sys.argv) < 2:
        print(json.dumps({"error": "No image path provided"}))
        sys.exit(1)
        
    try:
        image_path = sys.argv[1]
        image = face_recognition.load_image_file(image_path)
        encodings = face_recognition.face_encodings(image)
        
        if not encodings:
            print(json.dumps({"error": "No face detected"}))
            sys.exit(1)
            
        print(json.dumps(encodings[0].tolist()))
        
    except Exception as e:
        print(json.dumps({"error": str(e)}))
        sys.exit(1)

if __name__ == "__main__":
    main()