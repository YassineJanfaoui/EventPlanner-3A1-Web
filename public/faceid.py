import face_recognition
import sys
import json
import os

# Path to the user's uploaded image
image_path = sys.argv[1]
known_faces_dir = "./public/faces"  # Directory with known faces

# Check if the user's image exists
if not os.path.exists(image_path):
    print(json.dumps({"error": f"File not found: {image_path}"}))
    sys.exit(1)

# Check if the directory with known faces exists
if not os.path.isdir(known_faces_dir):
    print(json.dumps({"error": f"Directory not found: {known_faces_dir}"}))
    sys.exit(1)

# Load the user's image
unknown_image = face_recognition.load_image_file(image_path)
unknown_encodings = face_recognition.face_encodings(unknown_image)

# Ensure the user's image contains a face
if len(unknown_encodings) == 0:
    print(json.dumps({"match": "false", "error": "No face found in the uploaded image"}))
    sys.exit(0)

unknown_encoding = unknown_encodings[0]

# Iterate over all images in the known faces directory
match_found = False
for filename in os.listdir(known_faces_dir):
    filepath = os.path.join(known_faces_dir, filename)
    if not os.path.isfile(filepath):
        continue
    try:
        known_image = face_recognition.load_image_file(filepath)
        known_encodings = face_recognition.face_encodings(known_image)
        if len(known_encodings) == 0:
            continue
        known_encoding = known_encodings[0]
        results = face_recognition.compare_faces([known_encoding], unknown_encoding)
        if results[0]:
            match_found = True
            break
    except Exception as e:
        continue  # Optionally log the error

# Output the result
print(json.dumps({"match": str(match_found).lower()}))
