import os

TARGET_DIR = r"c:\Users\jorda\.gemini\antigravity\scratch\MyPetSitters\final_verify_V204\my-pet-sitters"

def audit_file(filepath):
    issues = []
    # 2. Garbage / content check
    try:
        with open(filepath, 'rb') as f:
            content = f.read()
            
        # Check for merge conflicts
        if b'<<<<<<< HEAD' in content:
            issues.append("Found Merge Conflict markers")
            
        # Check for weird characters (non-utf8 compatible or null bytes)
        if b'\0' in content:
            issues.append("Found NULL bytes (binary garbage)")
            
        # Check for BOM
        if content.startswith(b'\xef\xbb\xbf'):
            issues.append("Found BOM Header")
            
    except Exception as e:
        issues.append(f"Read Error: {e}")
        
    return issues

print(f"Scanning {TARGET_DIR}...")
all_issues = {}

for root, _, files in os.walk(TARGET_DIR):
    for file in files:
        if file.endswith(".php"):
            path = os.path.join(root, file)
            file_issues = audit_file(path)
            if file_issues:
                all_issues[path] = file_issues

if all_issues:
    print("ISSUES FOUND:")
    for p, i in all_issues.items():
        print(f"{p}: {i}")
else:
    print("clean: No garbage characters or merge markers found.")
