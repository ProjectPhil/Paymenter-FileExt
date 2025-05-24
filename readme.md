````markdown
# Paymenter FileExt 

An extension for [Paymenter](https://github.com/Paymenter/Paymenter) that adds secure file upload support to products—ideal for downloadable items like software, PDFs, images, and more.

## 📦 Features

- Upload files directly to a product in the admin panel
- Organizes files by product UUID
- File type and size validation
- Secure file handling and access
- Easily integrable into Paymenter’s admin UI

## 🧩 Usage

Once installed:

* Go to the admin panel
* Edit or create a product
* Upload files using the new "Product Files" section
* Files are stored in `/storage/product-uploads/<product-uuid>/`


## 🔐 Security

* Only admins can upload/manage product files
* File access routes are protected
* All files are scanned for extension/type

## 📜 License

This extension is open-sourced software licensed under the [MIT license](LICENSE).

---

Built for ❤️ [Paymenter](https://github.com/Paymenter/Paymenter)

---
