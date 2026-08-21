
import glob

for f in glob.glob("public/build/assets/app-*.css"):
    with open(f, "r", encoding="utf-8") as file:
        content = file.read()
        if "dropdown-toggle::after" in content:
            print("Found dropdown-toggle::after")
        if "[data-bs-toggle=\"dropdown\"]::after" in content:
            print("Found [data-bs-toggle=\"dropdown\"]::after")

