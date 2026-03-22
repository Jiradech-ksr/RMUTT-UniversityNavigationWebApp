import tensorflow as tf
from tensorflow.keras.preprocessing.text import Tokenizer
from tensorflow.keras.preprocessing.sequence import pad_sequences
import json
import numpy as np

# 1. Your RMUTT Dataset (Expand this!)
data = [
    {"text": "where is cpe", "intent": "navigation"},
    {"text": "how to get to building 3", "intent": "navigation"},
    {"text": "find the library", "intent": "navigation"},
    {"text": "what time does reg office close", "intent": "info"},
    {"text": "contact engineering faculty", "intent": "info"}
]

sentences = [d["text"] for d in data]
labels = [0 if d["intent"] == "navigation" else 1 for d in data]

# 2. Tokenization (Turning words into numbers)
tokenizer = Tokenizer(num_words=1000, oov_token="<OOV>")
tokenizer.fit_on_texts(sentences)
sequences = tokenizer.texts_to_sequences(sentences)
padded = pad_sequences(sequences, padding='post')

# 3. Simple Model
model = tf.keras.Sequential([
    tf.keras.layers.Embedding(1000, 16, input_length=len(padded[0])),
    tf.keras.layers.GlobalAveragePooling1D(),
    tf.keras.layers.Dense(16, activation='relu'),
    tf.keras.layers.Dense(2, activation='softmax') # 2 classes: Nav and Info
])

model.compile(loss='sparse_categorical_crossentropy', optimizer='adam', metrics=['accuracy'])
model.fit(padded, np.array(labels), epochs=100, verbose=0)

# 4. Save for use in your API
model.save("intent_model.h5")
with open("tokenizer.json", "w") as f:
    json.dump(tokenizer.to_json(), f)