# Order Updates & Net Position Maintaining Services

A robust, high-performance solution built for the **Indo-Thai Software Development Engineer Intern Take-Home Assessment**.

The system consists of two independent services:
1. **Order Update Service**: Reads order update records incrementally from a CSV source, validates each row according to the Event Contract, throttles emission rate, and streams valid events to the position service.
2. **Position Maintaining Service**: An HTTP service that maintains real-time net positions per trading symbol and guarantees event idempotency in memory.

---

## 🏛️ Architecture & Communication Choices

```
 +----------------------------------+        HTTP POST /events         +--------------------------------------+
 |       Order Update Service       | -------------------------------> |     Position Maintaining Service     |
 |  (Incremental CSV Reader,        |   Payload: JSON Order Event      |  (FastAPI Server, Thread-Safe        |
 |   Validator & Rate Throttle)     | <------------------------------- |   In-Memory Position & Event Store)  |
 +----------------------------------+          HTTP 200 OK / 400         +--------------------------------------+
                                                                                          |
                                                                                    GET /position
                                                                                          v
                                                                             { "RELIANCE": 90, "TCS": -75 }
```

### Why HTTP/REST with FastAPI?
- **Standardized & Lightweight**: HTTP/REST provides a clear, universally supported interface between the two services without requiring heavy external middleware (like Kafka or Redis).
- **Backpressure & Synchronous Delivery**: HTTP POST allows the Order Update Service to immediately handle delivery status (e.g. success, duplicate acknowledgement, or connection error).
- **Concurrency & Speed**: FastAPI (powered by Starlette and Uvicorn) provides asynchronous request handling with standard OpenAPI verification and high performance.
- **Thread Safety**: An explicit `threading.Lock()` inside `PositionStore` guarantees thread-safe, atomic position updates and consistent API reads under concurrent requests.

---

## 📋 Event Contract & Validation Schema

Each event transmitted between the services adheres strictly to the following JSON structure:

```json
{
  "event_id": "evt-0001",
  "symbol": "RELIANCE",
  "transaction_type": "BUY",
  "quantity": 90
}
```

### Contract Rules
- `event_id`: Non-empty string; unique identifier for the event.
- `symbol`: Non-empty string; symbol string preserving original case and value.
- `transaction_type`: Must be **exactly** `"BUY"` or `"SELL"`.
- `quantity`: Must be a **positive integer** (`> 0`). Zero, negative numbers, floats (e.g. `10.5`), and non-numeric strings are rejected.
- **Idempotency**: The first valid event received for an `event_id` wins. Subsequent events with the same `event_id` are safely ignored, even if other fields differ.
- **Fault Tolerance**: Invalid rows are logged at `WARNING` level with a clear reason and skipped without interrupting processing of subsequent rows.

---

## 🚀 Step-by-Step Setup & Quickstart

### 1. Prerequisites
- Python 3.9+ installed.

### 2. Install Dependencies
```bash
pip install -r requirements.txt
```

### 3. Run the Position Maintaining Service
Start the position maintaining service on `http://localhost:8000`:
```bash
python -m position_service.app --host 0.0.0.0 --port 8000
```

### 4. Run the Order Update Service
In a separate terminal, stream events from `order_updates.csv`:
```bash
python -m order_service.service --csv-path order_updates.csv --target-url http://localhost:8000/events --rate-limit 50.0
```

---

## 🧪 Running Automated Tests

Run the full automated test suite (including validation tests, position store tests, API endpoint tests, and end-to-end integration tests) using `pytest`:

```bash
pytest -v
```

### Test Coverage Highlights
- ✅ `BUY` and `SELL` position calculations (increasing/decreasing symbol quantities).
- ✅ Multiple symbols with positive, zero, and negative net positions.
- ✅ First-wins `event_id` duplicate event idempotency.
- ✅ Rejection of invalid transaction types (e.g. `HOLD`, `buy`, blank).
- ✅ Rejection of zero (`0`), negative (`-5`), float (`10.5`), non-numeric (`abc`), and blank quantities.
- ✅ Rejection of blank `event_id` and blank `symbol`.
- ✅ Continuation of processing after encounters with malformed/invalid rows.
- ✅ Exact structure and values returned by `GET /position`.
- ✅ Full end-to-end integration test spanning both services concurrently.

---

## ⚙️ Configuration Options

Both services support configuration via Command-Line Interface (CLI) arguments and environment variables:

| Service | Parameter | CLI Argument | Env Variable | Default | Description |
| :--- | :--- | :--- | :--- | :--- | :--- |
| **Position Service** | Host | `--host` | `HOST` | `0.0.0.0` | Host IP address to bind |
| **Position Service** | Port | `--port` | `PORT` | `8000` | Port number to listen on |
| **Order Service** | CSV Path | `--csv-path` | `CSV_PATH` | `order_updates.csv` | Input CSV file location |
| **Order Service** | Target Endpoint | `--target-url` | `TARGET_URL` | `http://localhost:8000/events` | Target API endpoint |
| **Order Service** | Rate Limit | `--rate-limit` | `RATE_LIMIT` | `50.0` | Max events emitted per second |

---

## 📡 API Usage & Example Responses

### 1. Submit Order Event
- **Endpoint**: `POST /events`
- **Request Body**:
  ```json
  {
    "event_id": "evt-0001",
    "symbol": "RELIANCE",
    "transaction_type": "BUY",
    "quantity": 90
  }
  ```
- **Response (200 OK - Accepted)**:
  ```json
  {
    "status": "accepted",
    "event_id": "evt-0001"
  }
  ```
- **Response (200 OK - Duplicate Ignored)**:
  ```json
  {
    "status": "ignored_duplicate",
    "event_id": "evt-0001"
  }
  ```

### 2. Fetch Net Positions
- **Endpoint**: `GET /position`
- **Response (200 OK)**:
  ```json
  {
    "RELIANCE": 90,
    "TCS": -75,
    "INFY": 0
  }
  ```

---

## ⚠️ Error Handling & Delivery Surfacing

- **Validation Failures**: Invalid rows detected during CSV processing generate detailed `WARNING` logs specifying row index, error cause, and row data. The service skips the bad row and continues.
- **HTTP / Connection Errors**: Network unreachable errors, timeouts, or unexpected HTTP status codes are logged at `ERROR` level. Failed sending count is tracked in final process summary stats.
- **Delivery Limitations**:
  - In-memory state: Positions and seen event IDs reside in memory. They will reset if the Position Maintaining Service restarts.
  - Delivery guarantee: Simple HTTP synchronous delivery (at-most-once without persistence queue).
