import requests
from telegram import Update
from telegram.ext import (
    ApplicationBuilder,
    MessageHandler,
    CommandHandler,
    ContextTypes,
    filters
)

# ================= CONFIG =================
BOT_TOKEN = "7933371142:AAFnPUtxWSQ3xzt2tTOa73Rr3yWwC4Uqnvc"
API_BASE = "https://mrnoface.top/magi/main.php?msisdn="
# ================= /start command =================
async def start(update: Update, context: ContextTypes.DEFAULT_TYPE):
    await update.message.reply_text(
        "🤖 Demo Bot Ready!\n\nযেকোনো টেক্সট পাঠাও, আমি API response দেখাবো।"
    )

# ================= Handle user message =================
async def handle_message(update: Update, context: ContextTypes.DEFAULT_TYPE):
    text = update.message.text.strip()

    if not text:
        await update.message.reply_text("❌ কিছু লিখে পাঠাও")
        return

    try:
        response = requests.get(API_BASE + text, timeout=30)
        data = response.json()
    except Exception:
        await update.message.reply_text("❌ API error")
        return

    msg = "📄 Result:\n"
    if isinstance(data, dict):
        for k, v in data.items():
            if isinstance(v, (str, int)):
                msg += f"• {k}: {v}\n"
    else:
        msg = "❌ Invalid API response"

    await update.message.reply_text(msg)

# ================= Bot run =================
app = ApplicationBuilder().token(BOT_TOKEN).build()

app.add_handler(CommandHandler("start", start))
app.add_handler(MessageHandler(filters.TEXT & ~filters.COMMAND, handle_message))

print("Bot running...")
app.run_polling()
