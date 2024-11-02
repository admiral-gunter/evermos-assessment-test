# Online Store Backend and Frontend Assessment

This project addresses the inventory and order processing issues experienced by an online store during a flash sale event, providing a proof-of-concept (PoC) solution with both backend API and frontend components.

## Task 1: API (Problem Analysis and Solution)

### Problem Analysis

The bad reviews stemmed from order cancellations due to stock unavailability after a successful 12.12 flash sale event.  This was caused by a combination of factors:

1. **Concurrency Issues:** The high traffic during the flash sale likely led to a race condition where multiple customers could simultaneously purchase the same item, even if the remaining stock was insufficient. The existing system likely didn't handle concurrent updates to the inventory properly, leading to overselling.

2. **Inadequate Inventory Management:** The fact that inventory quantities were misreported, even showing negative values, indicates a fundamental flaw in the inventory tracking system.  This could be due to delayed updates, lack of real-time synchronization, or bugs in the inventory update logic.

### Proposed Solution

To prevent future incidents, the following solution is proposed:

1. **Optimistic Locking for Inventory Updates:**  Implement optimistic locking at the database level during checkout. This strategy allows concurrent access to product records but prevents overselling by checking for changes to the inventory count before confirming an order. Each product record will include a version or timestamp field. During checkout, the current version/timestamp is read. When the order is finalized, the update operation includes a WHERE clause checking for the original version/timestamp. If the version/timestamp has changed (meaning another transaction updated the inventory), the update fails, and the user is notified that the item is no longer available. This avoids locking database rows for extended periods and improves overall performance.


## Technical Implementation (API)

The PoC API is implemented using Python and Flask.  It includes the following endpoints:

* **`/products` (GET):** Retrieves a list of available products with their details, including current stock quantity.
* **`/checkout` (POST):**  Handles the checkout process for a single product.  Implements
* **`/cart` (POST):**  Retrieves  a list of product in a cart
