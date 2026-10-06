index index.php;
location / { try_files $uri $uri/ /index.php?$query_string; }
location ~ /\.(?!well-known).* { deny all; }
client_max_body_size 50m;
fastcgi_buffer_size 32k;
fastcgi_buffers 8 32k;
