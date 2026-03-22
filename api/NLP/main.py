from fastapi import FastAPI
import tensorflow as tf
from tensorflow.keras.preprocessing.text import tokenizer_from_json
from tensorflow.keras.preprocessing.sequence import pad_sequences
import json

app = FastAPI()

# Load model and tokenizer
model = tf.keras.models.load_model("intent_model.h5")
with open("tokenizer.json") as f:
    tokenizer = tokenizer_from_json(json.load(f))

@app.get("/predict")
def predict_intent(q: str):
    # Process input
    seq = tokenizer.texts_to_sequences([q])
    padded = pad_sequences(seq, maxlen=6, padding='post')
    
    # Prediction
    pred = model.predict(padded)
    intent_idx = pred.argmax()
    
    # Extremely simple keyword/entity extraction by filtering conversational stop words
    stop_words = ["where", "is", "the", "how", "to", "get", "go", "find", "can", "you", "show", "me", "i", "want", "navigate", "direction", "directions", "building", "room", "what", "time", "does", "close", "open", "contact"]
    
    # Entity Resolution (Synonym Mapping)
    # Maps user slang/acronyms directly to the real database names
    synonyms = {
        "cpe": "computer engineering",
        "ee": "electrical engineering",
        "reg": "registration",
        "iwork": "i-work",
        "admin": "president"
    }
    
    words = q.lower().split()
    keywords = [w for w in words if w not in stop_words and len(w) > 1]
    
    # Translate any acronyms found into their full names
    mapped_keywords = [synonyms.get(k, k) for k in keywords]
    entity = " ".join(mapped_keywords) if mapped_keywords else q
    
    return {
        "intent": "navigation" if intent_idx == 0 else "info", 
        "confidence": float(pred.max()),
        "entity": entity
    }