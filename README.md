# FinHub Laravel Dashboard

A high-performance financial data dashboard built with **Laravel**, **PostgreSQL**, **Redis**, and **Docker**. This project tracks real-time market data, company news, and financial metrics using the Finnhub API, providing users with a personalized and dynamic stock portfolio view.

## ✨ Features

*   **User Authentication:** Secure user registration and login powered by Laravel Breeze.
*   **Personalized Stock Portfolio:**
    *   Users can add and manage stocks in their individual portfolios.
    *   Prevention of duplicate stock entries for a single user.
    *   Ability to remove stocks from the portfolio.
*   **Real-time Market Data:**
    *   Integration with the **Finnhub API** to fetch live stock quotes (current price, daily high/low/open/close, previous close).
    *   Display of 24-hour percentage change for each stock, color-coded (green for gain, red for loss).
*   **Comprehensive Company Information:**
    *   Fetches and displays detailed company profiles including name, logo, exchange, industry, website, market capitalization, IPO date, and shares outstanding.
    *   Displays the 3 most recent company news articles with headlines, summaries, and links to sources.
*   **Dynamic Dashboard:**
    *   Live-updating stock prices and 24-hour changes on the user's dashboard using **JavaScript polling** (updates every 10 seconds).
    *   Displays last updated timestamp, formatted to the user's local timezone.
    *   Clickable stock logos and symbols navigate to a dedicated stock detail page.
*   **Stock Search Functionality:**
    *   A dedicated search page allows users to find stocks by symbol.
    *   Automatic conversion of search queries to uppercase for accurate results (e.g., "aapl" becomes "AAPL").
    *   Displays live quote, company profile, and 24-hour change on search results.
*   **Dedicated Stock Detail Page:**
    *   Provides an in-depth view of a single stock, combining live quotes, full company profile details, and recent news articles in a clear, two-column layout.
*   **Market Pulse Dashboard:**
    *   Displays aggregated market insights: "Most Held Stocks" (based on user holdings), "Trending Adds" (based on recent user additions), and "Upcoming Earnings" for the next day.
    *   Data is pre-calculated and cached by a background scheduled console command for high performance and reduced API calls.
*   **Upcoming Earnings Page:**
    *   Dedicated page showing detailed earnings reports for *your saved stocks* for the next 20 days.
    *   Includes a chart visualizing earnings activity over the period.
    *   Detailed reports show symbol, date, time, EPS estimate, and revenue estimate.
*   **Performance & Scalability:**
    *   **Redis Caching:** Aggressively caches Finnhub API responses to minimize external API calls, reduce latency, and prevent rate-limiting issues. This includes pre-calculating and caching market pulse insights via a scheduled console command.

## 🚀 Architecture & Technologies

This project is built for speed and scalability using a modern containerized stack:

*   **Laravel (Sail):** The core PHP framework managing business logic, routing, and API orchestration.
*   **PostgreSQL:** Persistent storage for user data, stock portfolios, and other application data. Includes `stock_add_events` table for tracking trending stock additions.
*   **Redis:** High-speed caching layer to store Finnhub API responses, ensuring sub-millisecond data delivery to the application.
*   **Docker & Laravel Sail:** Full environment containerization to guarantee consistency between development, testing, and production environments.
*   **Finnhub API:** External service providing real-time and historical financial market data.
*   **Tailwind CSS:** A utility-first CSS framework for rapid and consistent UI development.
*   **JavaScript (Fetch API, DOM Manipulation):** For client-side polling and dynamic updates on the dashboard.
*   **Laravel Console Commands:** Scheduled commands (`pulse:update-cache`) are used to pre-process and cache complex market data, ensuring responsive UI and efficient API usage.

## 🛠️ Setup & Installation

Follow these steps to get the FinHub Dashboard up and running on your local machine.

### Prerequisites

*   **Docker Desktop:** Ensure Docker Desktop is installed and running on your system.
*   **Finnhub API Key:** Obtain a free API key from [Finnhub.io](https://finnhub.io/).

### Installation Steps

1.  **Clone the Repository:**
    ```bash
    git clone <your-repository-url>
    cd finhub-app
    ```

2.  **Install Composer Dependencies:**
    ```bash
    docker run --rm \
        -u "$(id -u):$(id -g)" \
        -v "$(pwd):/var/www/html" \
        -w /var/www/html \
        laravelsail/php82-composer:latest \
        composer install --ignore-platform-reqs
    ```

3.  **Copy Environment File:**
    ```bash
    cp .env.example .env
    ```

4.  **Configure `.env`:**
    Open the newly created `.env` file and update the following variables:
    *   `DB_CONNECTION=pgsql`
    *   `DB_HOST=pgsql`
    *   `DB_PORT=5432`
    *   `DB_DATABASE=laravel` (or your preferred database name)
    *   `DB_USERNAME=sail` (or your preferred username)
    *   `DB_PASSWORD=password` (or your preferred password)
    *   `REDIS_HOST=redis`
    *   `REDIS_PORT=6379`
    *   `FINNHUB_API_KEY=your_finnhub_api_key_here` (Replace with your actual Finnhub API key)

5.  **Start Docker Containers:**
    ```bash
    ./vendor/bin/sail up -d
    ```

6.  **Generate Application Key:**
    ```bash
    ./vendor/bin/sail artisan key:generate
    ```

7.  **Run Migrations:**
    ```bash
    ./vendor/bin/sail artisan migrate
    ```

8.  **Install NPM Dependencies & Compile Assets:**
    ```bash
    ./vendor/bin/sail npm install
    ./vendor/bin/sail npm run dev
    ```
9.  **Start the server and scheduler:**
    ```bash
    ./vendor/bin/sail up
    ./vendor/bin/sail artisan pulse:update-cache
    ```

10. **Access the Application:**
    Open your web browser and navigate to `http://localhost` (or the `APP_PORT` you configured in `.env`).

## 🚀 Usage

1.  **Register/Login:** Create a new user account or log in.
2.  **Search Stocks:** From the dashboard, click the "Search Stocks" button. Enter a stock symbol (e.g., `AAPL`, `MSFT`, `GOOG`) and click search.
3.  **Add to Portfolio:** On the search results page, if the stock data is valid, click "Add to Portfolio" to save it.
4.  **View Dashboard:** Your dashboard will display all your saved stocks with live prices, 24-hour changes, and last updated timestamps.
5.  **Stock Details:** Click on a stock's logo or symbol on the dashboard to view its dedicated detail page, including comprehensive company information and recent news.
6.  **Remove Stock:** On the dashboard, use the "Delete" button next to a stock to remove it from your portfolio.
