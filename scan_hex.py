import os

root = r'c:\Users\jorda\.gemini\antigravity\scratch\MyPetSitters\my-pet-sitters'
print(f"Scanning {root}...")

for filename in os.listdir(root):
    if filename.endswith('.php'):
        path = os.path.join(root, filename)
        with open(path, 'rb') as f:
            head = f.read(5)
            # Check for standard <?php (3c 3f 70 68 70)
            if head != b'<?php':
                print(f"SUSPECT: {filename} -> {head.hex()} ({head})")
