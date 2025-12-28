import os

def check_file(filepath):
    with open(filepath, 'rb') as f:
        content = f.read()
    
    # Check BOM
    if content.startswith(b'\xef\xbb\xbf'):
        print(f"BOM DETECTED: {filepath}")
        return

    # Check text before <?php
    try:
        text = content.decode('utf-8', errors='ignore')
        if '<?php' in text:
            preamble = text.split('<?php', 1)[0]
            if len(preamble) > 0:
                print(f"WHITESPACE/CONTENT BEFORE PHP: {filepath} (Length: {len(preamble)}) -> '{preamble}'")
        else:
            print(f"NO PHP TAG: {filepath}")
            
    except Exception as e:
        print(f"ERROR reading {filepath}: {e}")

root = r'c:\Users\jorda\.gemini\antigravity\scratch\MyPetSitters\my-pet-sitters'
print(f"Scanning {root}...")

for filename in os.listdir(root):
    if filename.endswith('.php'):
        check_file(os.path.join(root, filename))
