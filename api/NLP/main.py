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
    
    # English & Thai conversational stop words blocking noise
    stop_words = [
        "where", "is", "the", "how", "to", "get", "go", "find", "can", "you", "show", "me", "i", "want", "navigate", "direction", "directions", "building", "room", "what", "time", "does", "close", "open", "contact",
        "ไป", "ยังไง", "ทาง", "ไหน", "คือ", "อยู่", "ตึก", "อาคาร", "ห้อง", "เดิน", "ขอ", "ทราบ", "ทำ", "อยาก", "แผนที่", "นำทาง", "ที่", "รบกวน", "หน่อย", "ครับ", "ค่ะ", "มั๊ย", "ไหม", "สอบถาม"
    ]
    
    # Massive Bilingual NLP Translation Dictionary (Maps slang -> Database Entities)
    synonyms = {
        "cpe": "computer engineering",
        "วิศวะคอม": "computer engineering",
        "คอม": "computer engineering",
        "ee": "electrical engineering",
        "ไฟฟ้า": "electrical engineering",
        "วิศวะไฟฟ้า": "electrical engineering",
        "textile": "textile engineer",
        "สิ่งทอ": "textile engineer",
        "วิศวะสิ่งทอ": "textile engineer",
        "president": "office of the president",
        "admin": "office of the president",
        "อธิการ": "office of the president",
        "ตึกอธิการบดี": "office of the president",
        "iwork": "i-work",
        "helpdesk": "i-work",
        "ไอที": "i-work",
        "lab": "central laboratory",
        "แล็บ": "central laboratory",
        "แลป": "central laboratory",
        "avionic": "avionic engineering",
        "การบิน": "avionic engineering",
        "student": "student development",
        "กองพัฒ": "student development",
        "multipurpose": "multipurpose",
        "เอนกประสงค์": "multipurpose",
        "electronics": "electronics",
        "อิเล็ก": "electronics",
        "telecom": "telecommunication",
        "โทรคม": "telecommunication",
        "drama": "drama",
        "music": "music",
        "ดนตรี": "drama",
        "นาฏศิลป์": "music",
        "arch": "architecture",
        "สถาปัตย์": "architecture",
        "ถะปัด": "architecture",
        "reg": "registration"
    }
    
    # 1. Strip stop words by replacing them with empty spaces safely
    q_clean = q.lower()
    for sw in stop_words:
        q_clean = q_clean.replace(sw, " ")
    
    q_clean = " ".join(q_clean.split()) # Trim excess whitespace
    
    # 2. Substring matching for Thai/English slang to bypass spacing limits
    entity = q_clean
    for slang, db_entity in synonyms.items():
        if slang in q_clean:
            entity = db_entity
            break
            
    if not entity:
        entity = q
        
    return {
        "intent": "navigation" if intent_idx == 0 else "info", 
        "confidence": float(pred.max()),
        "entity": entity
    }