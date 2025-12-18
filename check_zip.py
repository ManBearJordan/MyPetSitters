import zipfile
import os

zip_path = r'c:\Users\jorda\.gemini\antigravity\scratch\MyPetSitters\my-pet-sitters.zip'

if not os.path.exists(zip_path):
    print("Zip file not found!")
else:
    try:
        with zipfile.ZipFile(zip_path, 'r') as zip_ref:
            print("Zip Contents (first 10 items):")
            for name in zip_ref.namelist()[:10]:
                print(name)
    except Exception as e:
        print(f"Error reading zip: {e}")
