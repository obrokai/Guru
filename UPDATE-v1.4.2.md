# Guru 主題 v1.4.2 更新清單

**版本**：1.4.2　**日期**：2026-11-25
**安裝檔**：`guru-theme-v1.4.2-20261125.zip`

測試站目前是 1.4.1。這版做客戶最新六項清單中可以直接改的項目：

1. **網站語言統一為英文（en-HK）**：網頁、Yoast Schema、社群分享（Open Graph）都標 en-HK，後台維持中文。
2. **文章顯示作者**：沒有指定作者的文章顯示「GURU Editorial Team」＋簡介＋發佈日期；Google 看到的作者（Schema）是同一個名字，不再是 admin。名稱和簡介在「外觀 → 自訂 → Insights articles」可改。
3. **文章底部可加相關服務／作品＋詢問按鈕**：編輯文章時右側「Related services & work」勾選要連結的項目。詢問區塊的文字也在「Insights articles」可改。
4. **About 標題「ABOUT GURU」排成一行**。
5. **Insights 標題改回和 About、Work 一樣大**。
6. 確認主題內沒有寫死測試站網址，搬到正式站不用改程式。

## 上傳後請確認
- [ ] 任一頁原始碼 `<html lang="en-HK">`，`og:locale` 為 en_HK，Yoast Schema `inLanguage` 為 en-HK
- [ ] 後台仍是中文
- [ ] 文章頁標題下有「By GURU Editorial Team · 日期」，內文後有作者欄（Written by／簡介／Published）
- [ ] 文章頁 Schema 的 author 是 GURU Editorial Team，頁面任何地方都沒有 admin
- [ ] 文章編輯畫面右側有「Related services & work」，勾選後前台出現相關服務、作品連結與詢問按鈕；按鈕跳到頁尾表單
- [ ] 沒勾選的文章只顯示詢問區塊
- [ ] About 標題一行；Insights 標題與 About／Work 同大小
- [ ] 手機 390 寬：上述各頁沒有橫向捲動

## 待客戶確認
- Editorial Team 簡介文字（目前是暫定英文）
- About 文字欄是否加寬與 Investor 一致：等 Manreen 決定是否放照片
