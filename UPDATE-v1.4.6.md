# Guru 主題 v1.4.6 更新清單

**版本**：1.4.6　**日期**：2026-11-26
**安裝檔**：`guru-theme-v1.4.6-20261126-slim.zip`

正式站目前是 1.4.5。這版加上客戶的 SEO 常見問答（89 題），**只給搜尋引擎，頁面上看不到**。

1. 後台 **Solutions → 編輯任一服務**：新增「SEO FAQ (search engines only — not shown on the page)」欄位，可以直接從 Word 貼上或修改。
2. **Solutions 頁**的原始碼多一段 FAQPage 結構化資料，頁面外觀不變。
3. 客戶這次的 89 題會在安裝後**第一次進後台**時自動填入。

## 上傳後請確認
- [ ] 外觀 → 佈景主題 顯示 1.4.6
- [ ] 進一次後台任一頁（讓問答自動填入），再打開 Solutions → Go-Global，「SEO FAQ」欄位有 12 題，下方寫「12 questions」
- [ ] 打開 https://www.guruonline.com.hk/solutions/ 的原始碼，搜尋 `FAQPage`，有 89 個 Question
- [ ] Solutions 頁外觀和之前一樣，看不到任何問答
- [ ] 用 Google 的 Rich Results Test 或 validator.schema.org 檢查 /solutions/，FAQPage 沒有錯誤

## 要跟客戶說的
- Influencer Marketing 的 Word 檔只有 Q1、Q8、Q10、Q11 四題，Q2–Q7 是不是漏了？
- China Digital Expansion 沒有問答。
- 之後要改問答，直接在每個服務的「SEO FAQ」欄位改，按更新即可。
