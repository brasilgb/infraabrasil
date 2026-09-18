index index.php;
location / { try_files $uri $uri/ /index.php?$query_string; }
location ~ /\.(?!well-known).* { deny all; }
client_max_body_size 50m;
