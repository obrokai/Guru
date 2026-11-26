# Guru 主題 v1.4.4 更新清單

**版本**：1.4.4　**日期**：2026-11-25
**安裝檔**：`guru-theme-v1.4.4-20261125.zip`

測試站目前是 1.4.3。這版是上線前準備：

1. **追蹤碼設定**：Customize → Guru site settings → **Tracking (Google)**，有三個欄位：
   - **Google Tag Manager container ID**：預設 `GTM-K5XVQ9V`（舊網站用的容器，裡面有 Google Ads 轉換追蹤）。
   - **Google Analytics 4 measurement ID**：預設空白。測試站的 Site Kit 已經接上 GA4（`G-NW4D4CJM18`）。用 Site Kit 或 GTM 加 GA4 時，這格要留空，否則會重複計算。
   - **Live site address**：預設 `https://www.guruonline.com.hk`。只有網站在這個網址時才會載入追蹤碼，所以**測試站不會載入**，搬到正式站後才會自動啟用。
2. **資料**（不在主題內，已直接加到測試站）：正式站 10-16 和 11-09 新增的 20 份投資人文件已補進測試站。網址代稱和 PDF 路徑都跟正式站相同，兩邊現在都是 1,065 份。

## 上傳後請確認
- [ ] Customize → Guru site settings 有「Tracking (Google)」，三個欄位都是上面的預設值
- [ ] 測試站首頁原始碼**沒有** `gtm.js`，`<head>` 裡有一行註解「Guru: Google Tag Manager is off at this address」
- [ ] Site Kit 的 GA4（`G-NW4D4CJM18`）照常出現，只出現一次
- [ ] Investor 頁 Last Announcement 最上面是 2026.11.06 PROFIT WARNING／盈利預告，往下 11.03、10.27、10.06、09.29、09.28（Investor 頁本來就只顯示最新 6 列）；按「View All」可看到全部 10 列，含 09.11 和 09.10（三列）。每列都有英文和中文 PDF
- [ ] 隨便點開其中一份 PDF 能下載（網址是 /staging/wp-content/uploads/2026/10/ 或 2026/11/）
- [ ] 其他頁面與 1.4.3 相同
