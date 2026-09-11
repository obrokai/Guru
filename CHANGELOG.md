# Guru 主題 — 版本紀錄

版本號記在 `guru-theme/style.css` 的 `Version:` 標頭（後台 外觀 → 佈景主題 會顯示），
安裝檔檔名為 `guru-theme-v<版本>-<日期>.zip`，例如 `guru-theme-v1.2.0-20260911.zip`。
壓縮檔內的資料夾一律是 `guru-theme`，所以上傳新版時 WordPress 會問「要用上傳的版本取代嗎」，選取代即可。

打包指令：`tools/package.sh`（會自動讀版本、加上今天的日期）。

---

## 1.2.0 — 2026-09-11

在客戶測試站（`/staging/`）實測後修正的一批問題。

- **客戶 logo 顏色錯誤**：主機的 ImageMagick 6.8.9 重新編碼 WebP 會破壞色彩，自動產生的縮圖全毀。客戶牆與首頁跑馬燈改用原始檔，匯入 logo 時不再產生縮圖。
- **導覽列顯示舊站選單**：主題選單改名 `Guru Primary` 並以 ID 追蹤；導覽列只在選單位置指向它時才使用 WordPress 選單，否則用內建的五個連結。
- **舊頁面佔住新網址**：匯入步驟 2 會把佔用 `/about/`、`/investor/` 等網址的舊頁改為草稿（加 `-old` 後綴），讓新頁接手；匯入的頁面身分改用 `_guru_seed_key` 判斷，不再只比對標題。
- **舊網址 301**：新增 `inc/redirects.php`，`about-us`、`our-work`、`contact-us`、`service/*`、`portfolio-item/*`、`last-announcement`、`financial-information` 等自動轉到新頁面。
- **一般頁面沒有版型**：新增 `page.php`，並清除舊頁殘留的 `[vc_row]` 等短碼。
- 首頁跑馬燈預設顯示原設計的六個品牌字標；Client logo 可勾選加入跑馬燈。
- 頁尾「Follow Us」在未填社群連結時整欄隱藏。
- 匯入步驟 2 一律重設首頁、網誌頁與主選單位置。
- 新增主題預覽圖 `screenshot.png`。

## 1.1.0 — 2026-09-10

與靜態版（Vercel）完全對齊，並修正多項功能問題。

- 首頁 About 統計區、首頁「Our Work」四格可指定（Showcase 的 Homepage position）。
- Solutions 各面板的手機截圖／影片輪播／單支影片／重點卡，可在後台編輯。
- About 頁世界地圖影片；Blog 分類篩選；Work 頁卡片與燈箱改回靜態版做法。
- Work／Blog 精簡頁尾、Solutions 無表單頁尾、Investor 頁獨立頁尾。
- Customizer 補齊所有設定（Homepage、About page、Blog、Investor page）。
- 表單來信改存獨立資料表 `{prefix}guru_enquiries`，可匯出 CSV；測試站推送正式站時排除該表即可保留來信。
- 匯入步驟 4 配合主機 30 秒執行上限可分次執行。
- 新增 `tools/`：本機 WordPress 測試環境、靜態站對照檢查、表單測試。

## 1.0.0 — 2026-08-24

第一版：全部頁面模板、四個自訂文章類型、一鍵匯入、客戶安裝說明。
