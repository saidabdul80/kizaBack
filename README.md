
# 📜 Transaction Filters - API Reference

Use the following filters in your API query strings to retrieve transactions based on specific criteria.

---

## 🔍 Search Filters

| Key               | Type     | Example           | Description                                 |
|------------------|----------|-------------------|---------------------------------------------|
| `search`          | `string` | `John`            | Searches recipients by name, phone, email, or bank. |
| `reference`       | `string` | `TXN12345`        | Filter by transaction reference.            |
| `type`            | `string` | `exchange`        | Filter by transaction type.                 |
| `status`          | `string` | `successful`      | Filter by status. See status enum values.   |
| `customer`        | `int`    | `23`              | Filter by customer ID.                      |

---

## 📆 Date Filters

| Key             | Type     | Example              | Description                              |
|----------------|----------|----------------------|------------------------------------------|
| `today`         | `bool`   | `true`               | Transactions from today.                 |
| `this_week`     | `bool`   | `true`               | Transactions from this week.             |
| `this_month`    | `bool`   | `true`               | Transactions from this month.            |
| `last_month`    | `bool`   | `true`               | Transactions from last month.            |
| `this_year`     | `bool`   | `true`               | Transactions from this year.             |
| `date_range`    | `array`  | `["2025-04-01", "2025-04-15"]` | Filter between two dates (inclusive). |

---

## 💰 Amount Filters

| Key               | Type     | Example       | Description                                |
|------------------|----------|---------------|--------------------------------------------|
| `min_amount`      | `number` | `1000`        | Minimum amount.                            |
| `max_amount`      | `number` | `5000`        | Maximum amount.                            |
| `amount_between`  | `array`  | `[1000, 5000]`| Amount range (use instead of min/max).     |

---

## 💱 Currency Filters

| Key               | Type     | Example | Description                      |
|------------------|----------|---------|----------------------------------|
| `currency_from`   | `int`    | `1`     | Filter by source currency ID.    |
| `currency_to`     | `int`    | `2`     | Filter by destination currency ID.|

---

## 👤 Recipient Filters

| Key                 | Type     | Example        | Description                         |
|--------------------|----------|----------------|-------------------------------------|
| `recipient_name`    | `string` | `Jane Doe`     | Matches recipient or saved name.    |
| `recipient_phone`   | `string` | `08012345678`  | Matches recipient or saved phone.   |
| `recipient_email`   | `string` | `jane@mail.com`| Matches recipient or saved email.   |
| `recipient_bank`    | `string` | `GTBank`       | Matches recipient or saved bank.    |

---

## 🔢 Sorting

| Key         | Type     | Example       | Description                              |
|-------------|----------|---------------|------------------------------------------|
| `created_at`| `string` | `desc` or `asc`| Sort by created date (default is `desc`).|

---

## 🔗 Example Usage

```http
GET /api/transactions?min_amount=1000&max_amount=5000&status=successful&this_month=true
```

This will return all successful transactions made this month with amounts between 1,000 and 5,000.

---

> For more help, contact the backend team or check the TransactionFilter class.
