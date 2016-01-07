ponyshots
=========

An insanely simple screenshot uploading "web app".

Seriously, it doesn't really get any simpler than this.

Copy inc/db.example.php to inc/db.php and edit it with your MySQL database credentials. Also, you probably want to import schema.sql from this repo.

Insert a row or two containing usernames and API keys so that you can actually use the service. How to do so should be self-explanatory. The password field in the users table is currently unused.

I have a rewrite set up to route `https://mydomain.tld/ps/(.*)` to `/ps/index.php?page=$1` in nginx.

To upload a screenshot, hit /upload with a multipart POST containing:

* Your username in the `username` field
* Your API key in the `apikey` field
* Your image file in the `image` field

You'll get a JSON response that looks something like this, for success:

`{"error": false, "hash": "<sha1 of your image>", "slug": "<image slug>", "extension": "<image file extension>"}`

Or if there was an error, it'll look like this:

`{"error": true, "message": "<descriptive error message as to why there was a failure>"}`

The slug is how the image can be shared. I use an nginx block that looks something like this:

```nginx
location ~ /[a-zA-Z0-9]+$ {
    alias /var/www/ps/images;
    try_files $uri.png $uri.jpg $uri.jpeg $uri.gif /404.php;
}
```

This way, images will be accessible at `https://mydomain.tld/<slug>`.

That's about all there is to it, really.
