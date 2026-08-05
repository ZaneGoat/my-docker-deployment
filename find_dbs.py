import os
import re

deploy_dir = "/home/Zane/Desktop/my-docker-deployment/deploy"
if not os.path.exists(deploy_dir):
    deploy_dir = r"C:\Users\user\Desktop\ZaneX\my-docker-deployment\deploy"

dbs = set()
for root, dirs, files in os.walk(deploy_dir):
    if "venv" in root or "node_modules" in root: continue
    for f in files:
        if f.endswith('.py') or f.endswith('.php') or f.endswith('.env'):
            path = os.path.join(root, f)
            try:
                with open(path, 'r', encoding='utf-8') as file:
                    content = file.read()
                
                # find mysql://.../dbname
                matches = re.findall(r'mysql(?:\+pymysql)?://[^/]+/([^?\'"\s]+)', content)
                for match in matches:
                    dbs.add(match)
                
                # Also check for PHP mysqli_connect / PDO
                if f.endswith('.php'):
                    matches = re.findall(r'mysqli_connect\(.*?,.*?,.*?,.*?[\'"]([^\'"]+)[\'"]\)', content)
                    for match in matches:
                        dbs.add(match)
                    matches = re.findall(r'dbname=([^;\'"]+)', content)
                    for match in matches:
                        dbs.add(match)

            except Exception as e:
                pass

print("DATABASES:", dbs)
