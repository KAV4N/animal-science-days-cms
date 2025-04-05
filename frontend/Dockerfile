FROM node:20-alpine

# Set working directory
WORKDIR /app

# Install dependencies
COPY package*.json ./
RUN npm install

# Copy app source
COPY . .

# Expose port and start the app in development mode
EXPOSE 5173
CMD ["npm", "run", "dev", "--", "--host", "0.0.0.0"]