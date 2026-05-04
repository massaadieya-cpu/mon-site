import numpy as np
from flask import Flask, request, jsonify
from sentence_transformers import SentenceTransformer
from sklearn.metrics.pairwise import cosine_similarity

app = Flask(__name__)

# Modèle performant pour le Français et l'Arabe (Parfait pour le contexte Tunisien)
model = SentenceTransformer('paraphrase-multilingual-MiniLM-L12-v2')

# Base de connaissances étendue
RH_KNOWLEDGE = {
    "rémunération": {
        "sol": "Réviser la grille salariale et introduire des primes de performance.",
        "anchors": ["salaire paye argent prime augmentation bonus revenu solde", "راتب زيادة منحة فلوس"]
    },
    "charge_travail": {
        "sol": "Réévaluer la répartition des tâches et instaurer des limites de temps.",
        "anchors": ["stress pression fatigue burn-out épuisement surcharge trop de travail", "تعب ضغط شغل برشا"]
    },
    "climat_social": {
        "sol": "Organiser des sessions de médiation ou de team building.",
        "anchors": ["ambiance collègues équipe relations conflit groupe communication", "جو مشاحنات زملاء"]
    },
    "équilibre_vie_pro": {
        "sol": "Favoriser le télétravail et la flexibilité horaire.",
        "anchors": ["famille horaires repos loisirs équilibre enfant week-end", "وقت عائلة دار"]
    },
    "évolution": {
        "sol": "Proposer des plans de formation et clarifier les perspectives de carrière.",
        "anchors": ["formation promotion carrière apprendre futur perspective grade", "ترقية تعلم مستقبل"]
    }
}

# Pré-calcul des embeddings des catégories (Vecteurs moyens)
CAT_KEYS = list(RH_KNOWLEDGE.keys())
CAT_EMBEDDINGS = [model.encode(" ".join(RH_KNOWLEDGE[k]["anchors"])) for k in CAT_KEYS]

def analyze_text(text):
    if not text or len(text.strip()) < 5:
        return None, 0
    
    query_vec = model.encode([text])
    sims = cosine_similarity(query_vec, CAT_EMBEDDINGS)[0]
    idx = np.argmax(sims)
    
    return (CAT_KEYS[idx], float(sims[idx]))

@app.route('/predict', methods=['POST'])
def predict():
    data = request.json
    corpus = list(set(data.get("corpus", []))) # Dédoublonnage
    stats_faibles = data.get("stats_faibles", [])
    
    recs = []

    # 1. Analyse des questions à score faible
    for q in stats_faibles:
        cat, score = analyze_text(q['titre'])
        if score > 0.35:
            recs.append({
                "type": "Alerte Statistique",
                "probleme": q['titre'],
                "solution": RH_KNOWLEDGE[cat]["sol"],
                "confiance": round(score * 100, 1)
            })

    # 2. Analyse des commentaires libres
    for comment in corpus:
        cat, score = analyze_text(comment)
        if score > 0.45: # Seuil plus strict pour le texte libre
            recs.append({
                "type": "Sentiment Collaborateur",
                "probleme": f"« {comment} »",
                "solution": RH_KNOWLEDGE[cat]["sol"],
                "confiance": round(score * 100, 1)
            })

    return jsonify({
        "recommandations": sorted(recs, key=lambda x: x['confiance'], reverse=True)[:6]
    })

if __name__ == '__main__':
    app.run(host='0.0.0.0', port=5000)