# Use the official Node.js 20 Alpine image as a base
FROM node:20-alpine

# Set the working directory in the container
WORKDIR /app

# Copy package.json and package-lock.json to leverage Docker cache
# Use the 'node' user and group for ownership
COPY --chown=node:node package.json ./

# Install project dependencies
RUN npm install

# Copy the rest of the application source code
COPY --chown=node:node . .

# Expose port 3000 to the outside world
EXPOSE 3000

# Switch to the non-root user (node user)
USER node

# The default command to run when the container starts
CMD ["npm", "run", "dev"]
