# **AIUB Lost & Found** is a full-stack PHP + MySQL web application designed specifically for the AIUB campus community. Students and staff can report lost or found items, and the system's built-in **smart matching engine** automatically cross-compares new reports against existing ones using multiple criteria — category, keywords, color, brand, location, and date proximity — scoring similarity from **0 to 100%**.

# When a match crosses the threshold, **both parties are instantly notified** via email and an in-app notification, maximising the chance of reuniting people with their belongings.

## 🧠 How the Matching Algorithm Works

# The core of the platform is `includes/matching.php` → `calculate_match_score()`.
# Every time a new lost or found item is submitted, the engine scores it against **all existing open reports** using this weighted rubric:

# ```
┌─────────────────────────────────────┬────────┐
│ Criterion                           │ Points │
├─────────────────────────────────────┼────────┤
│ Category match                      │  30 pt │
│ Item name / description overlap     │  30 pt │
│ Color match                         │  10 pt │
│ Brand match                         │  10 pt │
│ Location similarity (word overlap)  │  10 pt │
│ Date logic (found >= lost, <=30 d)  │  10 pt │
└─────────────────────────────────────┴────────┘
                              Total → 100 pt
# ```

A match record is created and notifications are sent when the score >= **MATCH_THRESHOLD** (default: **55%**, configurable in `config/database.php`).
