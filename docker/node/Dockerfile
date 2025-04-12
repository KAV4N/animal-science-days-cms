# docker/node/Dockerfile
FROM node:20-alpine

# Set working directory
WORKDIR /app

# Copy package.json and package-lock.json
COPY package*.json ./

# Install dependencies
RUN npm install

# Copy the rest of the frontend application
COPY . .

# Expose port 5173 for Vite development server
EXPOSE 5173

# Run the Vite development server with --host 0.0.0.0
CMD ["npm", "run", "dev", "--", "--host", "0.0.0.0"]