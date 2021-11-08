# HTTPS on local XAMPP

1. Make sure you have `C:\xampp\apache\conf\ssl.crt\server.crt` and `C:\xampp\apache\conf\ssl.key\server.key`.

If you don't have them, or if the existing are expired or have other problems, then run `C:\xampp\apache\makecert.bat` as admin.

You may need to tweek `makecert`: https://community.apachefriends.org/viewtopic.php?p=256423&sid=5118db64708ed66ea89f6b3481237986#p256489 (if have `subjectAltName` problem)

2. Change some files in Apache

- https://gist.github.com/adnan360/ad2b1cfc44114ac6f91fbb668c76798d
- https://gist.github.com/nguyenanhtu/33aa7ffb6c36fdc110ea8624eeb51e69
- https://florianbrinkmann.com/en/https-virtual-hosts-xampp-4215/
- https://codelapan.com/post/how-to-install-ssl-certificate-on-xampp

Other potential problems:
- https://www.ibm.com/support/pages/how-resolve-browser-certificate-warning-site-missing-valid-trusted-certificate
