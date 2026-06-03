# Project Specification: Invest News Monitor

## 🎯 Project Overview
Invest News Monitor is a web application built to automate the collection, triage, and deep analysis of financial news. The system utilizes a multi-tier AI approach: using lightweight, free models for rapid initial categorization (triage) and sophisticated, paid models for deep financial reasoning on high-impact news.

## 🛠 Technology Stack
- **AI Orchestration:** OpenRouter API (integrating Gemini Flash for triage and Claude 3.5/GPT-4o for reasoning)

---

## 📋 Core Functional Features

### 1. Automated News Ingestion (The Collector)
- **Source:** `https://www.investing.com/rss/investing_news.rss`
- **Schedule:** Runs every 5 minutes via Laravel Scheduler.
- **Mechanism:** - Parse the XML feed and extract: Title, Link, PubDate, Author, and Enclosure (image).
    - Use `firstOrCreate` logic on the `link` unique field to prevent duplicates.
    - New entries are saved with an `unprocessed` status.

### 2. AI-Driven Triage (The Filter)
- **Goal:** Identify market-moving news without human intervention.
- **Process:** - Every 5 minutes, the system selects unprocessed news titles.
    - It constructs a batch prompt containing the titles and the current "Impact Rules" defined in the settings.
    - Calls a **Free Model** via OpenRouter.
- **Dynamic Rules (Editable in DB):**
    - **Type A:** Potential significant drop in share price by [X]%.
    - **Type B:** Potential significant rise in share price by [X]%.
    - **Type C:** Significant drop occurred, potential "dead cat bounce" or recovery of [X]%.
- **Action:** Updates the `category_id` of the news item based on the AI's verdict.

### 3. Deep Reasoning (The Analyst)
- **Trigger:** On-demand via the "Deep Reasoning" button on the Dashboard.
- **Logic:**
    1. Fetch the full article content by using Laravel + Symfony DomCrawler.
    2. Submit the full text to a **Paid Reasoning Model** (e.g., Claude 3.5 Sonnet).
    3. **Prompt:** Expert financial analysis focusing on sentiment, company health metrics mentioned, and price targets.
    4. Save the response to the `deep_analyses` table.
    5. Allow the user to "Regenerate" the analysis if needed.

### 4. Data Lifecycle Management (Cleanup)
- **Schedule:** Hourly.
- **Logic:** Deletes all `news_items` and associated `deep_analyses` older than a user-defined retention period (e.g., 7 days).

---

## 🗄 Database Structure (Conceptual)

- **news_items:** `id`, `title`, `link` (unique), `author`, `image_url`, `pub_date`, `category_id` (null by default).
- **category_types:** `id`, `name`, `prompt_logic` (text), `x_value` (integer/float).
- **settings:** `key` (unique), `value`.
- **deep_analyses:** `id`, `news_item_id` (foreign), `content` (text), `updated_at`.

---

## 🖥 User Interface Layout

### Dashboard (Inertia Page)
- **Impact Section:** A highlighted top-grid showing news filtered by the AI triage.
    - Each card shows the "Impact Type" badge.
    - Action buttons: [Deep Reasoning] or [View Summary].
- **General Feed:** A paginated table (50 items/page) of all incoming news.
- **States:** Implement loading skeletons for AI calls and "No news found" placeholders.

### Rule Management (Inertia Page)
- CRUD interface to manage `category_types`.
- Define the percentage [X] and the specific prompt instructions the AI should follow when evaluating a headline.

### Application Settings
- Input for "Retention Days" (the X value for database cleanup).
- API Key management for OpenRouter.

---

## 🚦 Execution Guidelines for Development
1. **Migrations:** Set up the schema with UUIDs for primary keys.
2. **Commands:** Implement `app:fetch-news`, `app:triage-news`, and `app:cleanup`.
3. **Services:** Create an `OpenRouterService` for centralized AI handling.