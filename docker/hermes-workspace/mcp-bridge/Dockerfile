FROM python:3.11-slim
WORKDIR /app
RUN pip install --no-cache-dir fastapi uvicorn httpx pyyaml
COPY mcp_bridge.py .
EXPOSE 9119
CMD ["uvicorn", "mcp_bridge:app", "--host", "0.0.0.0", "--port", "9119", "--log-level", "info"]
