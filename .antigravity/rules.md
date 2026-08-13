# PROJECT RULES — Token-Efficient Development Mode

## 1. Core Principle
- Prioritize minimal token usage in every response.
- Do NOT explain basic/obvious things unless explicitly asked.
- No repeated summaries of code that hasn't changed.
- Skip preambles like "Sure, here's..." — go straight to the answer/code.

## 2. Code Output Rules
- Only output the changed code (diff-style or specific function/block), 
  NOT the entire file, unless the whole file is genuinely new or requested.
- No placeholder comments like "// rest of code remains same" repeated unnecessarily — 
  just state clearly what changed and where (file + line/function reference).
- Avoid regenerating unchanged boilerplate, imports, or config on every response.

## 3. Explanation Rules
- Default to short, direct explanations (max 3-4 lines) unless asked for details.
- No repeating the question back before answering.
- No unnecessary "best practices" lecture unless asked.

## 4. Existing Project Safety
- NEVER modify, delete, or refactor existing working code/files unless explicitly 
  instructed for that specific task.
- Any new feature/fix must be additive and isolated — don't touch unrelated modules, 
  routes, migrations, or configs.
- Before suggesting a structural change, ASK first — don't auto-apply.
- Always assume backward compatibility must be preserved unless told otherwise.
- Contest voting: A user is allowed to vote for multiple contestants/businesses in a round, but cannot cast a vote multiple times (double vote) for the same business in the same round, nor vote for their own business.

## 5. Self-Updating Rules (Learning Mode)
- Whenever a new instruction, preference, or correction is given during a task, 
  treat it as a permanent rule update for this project — append it to this rules 
  file under the correct section automatically, without asking to repeat it.
- If a rule conflicts with a new instruction, the newer instruction takes priority — 
  update silently and continue.
- Keep this rules file lightweight — merge duplicate/similar rules instead of stacking.

## 6. Communication Style
- Reply in short, direct sentences.
- Code-first, explanation-after (only if needed).
- If a task is ambiguous, ask ONE clarifying question — don't guess with a long response.
