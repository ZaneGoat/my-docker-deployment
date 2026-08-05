import os
import re

dirs = [r"C:\Users\user\Desktop\ZaneX\my-docker-deployment\deploy\ayarestoPHP", 
        r"C:\Users\user\Desktop\ZaneX\my-docker-deployment\deploy\projrtZL"]

for d in dirs:
    for root, _, files in os.walk(d):
        for f in files:
            if f.endswith('.php'):
                path = os.path.join(root, f)
                try:
                    with open(path, 'r', encoding='utf-8') as file:
                        content = file.read()
                    matches = re.findall(r'\$dbname\s*=\s*[\'"]([^\'"]+)[\'"]', content)
                    if matches:
                        print(f"Found in {path}: {matches}")
                    matches = re.findall(r'mysqli_connect\(.*?,.*?,.*?,.*?[\'"]([^\'"]+)[\'"]\)', content)
                    if matches:
                        print(f"Found in {path}: {matches}")
                except Exception as e:
                    pass
