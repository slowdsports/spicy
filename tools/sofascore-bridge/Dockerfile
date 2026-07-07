FROM mcr.microsoft.com/playwright:v1.61.1-jammy

WORKDIR /app
COPY package*.json ./
RUN npm install --omit=dev
COPY server.js robustGoto.js ./

ENV PORT=4321
EXPOSE 4321
CMD ["node", "server.js"]
