FROM nginx:alpine

# Copy your local website files into Nginx's default directory
COPY . /usr/share/nginx/html

