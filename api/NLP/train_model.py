import tensorflow as tf
from tensorflow.keras.preprocessing.text import Tokenizer
from tensorflow.keras.preprocessing.sequence import pad_sequences
import json
import numpy as np
import matplotlib.pyplot as plt

#Dataset
data = [

    {"text": "where is cpe", "intent": "navigation"},
    {"text": "how to get to building 3", "intent": "navigation"},
    {"text": "find the library", "intent": "navigation"},
    {"text": "take me to engineering faculty", "intent": "navigation"},
    {"text": "navigate to the central laboratory", "intent": "navigation"},
    {"text": "directions to multipurpose building", "intent": "navigation"},
    {"text": "show me the way to iwork", "intent": "navigation"},
    {"text": "how do i go to architecture", "intent": "navigation"},
    {"text": "where can I find textile building", "intent": "navigation"},
    {"text": "electrical engineering location", "intent": "navigation"},
    {"text": "finding room 405", "intent": "navigation"},
    {"text": "go to the cafeteria", "intent": "navigation"},
    
    # Navigation - Thai
    {"text": "ไปวิศวะคอมยังไง", "intent": "navigation"},
    {"text": "ตึกอธิการบดีอยู่ไหน", "intent": "navigation"},
    {"text": "ขอเส้นทางไปห้องสมุดหน่อย", "intent": "navigation"},
    {"text": "นำทางไปตึก 3", "intent": "navigation"},
    {"text": "เดินไปสถาปัตย์ทางไหน", "intent": "navigation"},
    {"text": "อยากไปแล็บกลาง", "intent": "navigation"},
    {"text": "ช่วยหาห้องน้ำที", "intent": "navigation"},
    {"text": "ไปอาคารเอนกประสงค์ยังไงครับ", "intent": "navigation"},
    {"text": "อยากทราบทางไปดนตรีและนาฏศิลป์", "intent": "navigation"},
    {"text": "ตึกโทรคมนาคมอยู่ตรงไหน", "intent": "navigation"},
    {"text": "ทางไปวิศวะไฟฟ้า", "intent": "navigation"},
    {"text": "ทางไปตึกอีเอ็น", "intent": "navigation"},

    # Info - English
    {"text": "what time does reg office close", "intent": "info"},
    {"text": "contact engineering faculty", "intent": "info"},
    {"text": "what are the hours for the library", "intent": "info"},
    {"text": "telephone number of office of the president", "intent": "info"},
    {"text": "who is the dean of engineering", "intent": "info"},
    {"text": "tell me about student development", "intent": "info"},
    {"text": "is iwork open right now", "intent": "info"},
    {"text": "information about rmutt university", "intent": "info"},
    {"text": "when does the central laboratory open", "intent": "info"},
    
    # Info - Thai
    {"text": "ห้องทะเบียนปิดกี่โมง", "intent": "info"},
    {"text": "ช่องทางติดต่อคณะวิศวกรรมศาสตร์", "intent": "info"},
    {"text": "ห้องสมุดเปิดวันไหนบ้าง", "intent": "info"},
    {"text": "ขอเบอร์โทรศัพท์ตึกอธิการ", "intent": "info"},
    {"text": "กองพัฒเปิดไหมวันนี้", "intent": "info"},
    {"text": "ใครคือคณบดีวิศวะ", "intent": "info"},
    {"text": "สอบถามข้อมูลเกี่ยวกับมทรธัญบุรี", "intent": "info"},
    {"text": "ศูนย์อาหารปิดกี่โมงคะ", "intent": "info"},
    {"text": "ติดต่อไอทีได้ที่ไหน", "intent": "info"}
]

sentences = [d["text"] for d in data]
labels = [0 if d["intent"] == "navigation" else 1 for d in data]

MAX_LEN = 20

#Tokenization (words to numbers)
tokenizer = Tokenizer(num_words=1000, oov_token="<OOV>")
tokenizer.fit_on_texts(sentences)
sequences = tokenizer.texts_to_sequences(sentences)
padded = pad_sequences(sequences, maxlen=MAX_LEN, padding='post', truncating='post')

#Sequential Model
model = tf.keras.Sequential([
    tf.keras.layers.Embedding(1000, 16, input_length=MAX_LEN),
    tf.keras.layers.GlobalAveragePooling1D(),
    tf.keras.layers.Dense(16, activation='relu'),
    tf.keras.layers.Dense(2, activation='softmax') # 2 classes: Nav and Info
])

model.compile(loss='sparse_categorical_crossentropy', optimizer='adam', metrics=['accuracy'])
history = model.fit(padded, np.array(labels), epochs=150, verbose=0)

# Save for use in API
model.save("intent_model.h5")
with open("tokenizer.json", "w") as f:
    json.dump(tokenizer.to_json(), f)

# 5. Plot Training Accuracy and Loss
plt.figure(figsize=(10, 4))

# Plot Accuracy
plt.subplot(1, 2, 1)
plt.plot(history.history['accuracy'], label='Accuracy', color='blue')
plt.title('Training Accuracy')
plt.ylabel('Accuracy')
plt.xlabel('Epoch')
plt.legend()

# Plot Loss
plt.subplot(1, 2, 2)
plt.plot(history.history['loss'], label='Loss', color='red')
plt.title('Training Loss')
plt.ylabel('Loss')
plt.xlabel('Epoch')
plt.legend()

plt.tight_layout()
plt.savefig('training_graph.png')
print("Graph saved successfully as training_graph.png!")