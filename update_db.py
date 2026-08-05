import os

deploy_dir = "/home/Zane/Desktop/my-docker-deployment/deploy"

for root, dirs, files in os.walk(deploy_dir):
    for f in files:
        if f.endswith('.php') or f.endswith('.py'):
            path = os.path.join(root, f)
            try:
                with open(path, 'r', encoding='utf-8') as file:
                    content = file.read()
                
                new_content = content.replace('"localhost"', '"mysql"')
                new_content = new_content.replace("'localhost'", "'mysql'")
                new_content = new_content.replace("'127.0.0.1'", "'mysql'")
                new_content = new_content.replace('"127.0.0.1"', '"mysql"')
                
                if new_content != content:
                    with open(path, 'w', encoding='utf-8') as file:
                        file.write(new_content)
                    print(f"Updated {path}")
            except Exception as e:
                pass
