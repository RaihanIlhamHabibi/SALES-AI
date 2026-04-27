# SALES-AI

AI-powered sales page generator built with Laravel.

## Features

- Authentication and user-specific sales pages
- AI generation with OpenRouter, Gemini, or OpenAI
- Multiple preview styles: classic, bold, minimal
- Section-by-section regeneration (headline, CTA, etc.)
- Export sales page as standalone HTML

## Quick Start

1. Install dependencies:
   - `composer install`
   - `npm install`
2. Configure `.env` and set database + AI API keys.
3. Run migrations:
   - `php artisan migrate`
4. Start app:
   - `php artisan serve`
   - `npm run dev`
