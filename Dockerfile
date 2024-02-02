# Use the official Nginx image as the base image
FROM nginx:latest

# Copy the local index.html file to the default Nginx HTML directory
COPY . /usr/share/nginx/html/

# Expose port 80, which is the default port for Nginx
EXPOSE 80
