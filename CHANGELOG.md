# Guru 主題 — 版本紀錄

版本號記在 `guru-theme/style.css` 的 `Version:` 標頭（後台 外觀 → 佈景主題 會顯示），
安裝檔檔名為 `guru-theme-v<版本>-<日期>.zip`，例如 `guru-theme-v1.2.0-20260911.zip`。
壓縮檔內的資料夾一律是 `guru-theme`，所以上傳新版時 WordPress 會問「要用上傳的版本取代嗎」，選取代即可。

打包指令：`tools/package.sh`（會自動讀版本、加上今天的日期）。

---

## 1.2.4 — 2026-09-22

- **Work 頁版面被封面圖撐壞**：客戶第一次上傳封面圖後，整頁變成「一大兩小」、右邊兩欄擠成小方塊。原因是卡片內的 `<img>` 沒有填滿規則，1024px 原圖把 grid 欄位撐開（靜態站的卡片只有色塊與影片，從未有真的圖片，所以這條規則一直沒被需要）。補上與影片相同的 `position:absolute; inset:0; object-fit:cover`，並讓卡片 `min-width:0`。首頁「Our Work」四格同樣補上，避免客戶把有圖的作品放上首頁時出現同樣狀況。

## 1.2.3 — 2026-09-22

- **修正 1.2.1／1.2.2 的字級錯誤（重要）**：Typography 功能在預設 100% 時沒有定義 CSS 變數，`calc(… * var(--guru-fs-…))` 因此整條失效，各頁大標題、區塊標題退回繼承的內文字級（About 頁「ABOUT GURU」縮成 16px）。產生器改為 `var(--guru-fs-…, 1)` 帶預設值；本機以算出的字級驗證（About 大標題 115px、內文 17px，與設計相同）。**若已安裝 1.2.1 或 1.2.2，請務必更新到這版。**
- 客戶教學文件加入後台實際畫面截圖（7 張，繁體中文介面、紅框標示），`docs/客戶教學-上架作品與調整字體.html` 與 `docs/images/`。

## 1.2.2 — 2026-09-15

- **Showcase details／Solution media 欄位移到標題正下方、內文編輯器之上**。1.2.1 改用傳統編輯畫面後，欄位仍排在內文編輯器下面（WordPress 的預設位置），要往下捲才看得到；現在打開編輯畫面第一眼就是這些欄位。
- 新增客戶教學文件 `docs/客戶教學-上架作品與調整字體.md`。

## 1.2.1 — 2026-09-15

客戶在測試站上架作品時找不到欄位、上傳失敗（客戶回饋 9/15）。

- **Showcase 與 Solutions 改用傳統編輯畫面**（不再是區塊編輯器）。原本「Showcase details」被擠到頁面最底下、「Cover image」藏在可收合的右側欄，客戶只看到標題和一片空白，於是把封面圖貼進內文區。現在欄位直接在標題下方、封面圖在右欄，一眼可見。
- Showcase 編輯畫面頂端加一行提示：封面圖請用右欄的 Cover image。
- **上傳大圖失敗（「無法從伺服器取得有效的回應」）**：主機 PHP 每次請求只有 30 秒，大圖在產生縮圖時超時。新增 `inc/media.php`：上傳期間放寬時間上限，並停止產生主題用不到的 1536px／2048px 兩個尺寸（最耗時的兩張）。
- README 新增圖片／影片尺寸建議（JPG ≤ 2000px ≤ 2 MB；影片 ≤ 30 MB），以及上傳失敗時要抄下左下角的錯誤訊息。
- **新增字體大小調整**（客戶要求）：外觀 → 自訂 → Guru site settings → **Typography**，四個百分比：內文、區塊標題、頁面大標題、導覽列連結（70–150%，預設 100%）。100% 時與原設計完全相同；調整時保留所有響應式斷點。實作方式是由 `tools/build-typography.py` 從主題 CSS 自動產生 `assets/css/type/*.css`，用 CSS 變數乘上原本的字級，不修改原有樣式。

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
- **Work 頁沒有影片的作品**：點擊後改為開啟該作品自己的頁面（原本會彈出空白燈箱）。
- **Work 頁瀏覽器分頁標題**改為「Work」（原本顯示後台用的「Showcase」）。
- **Blog 分類篩選**：選到沒有文章的分類時顯示「No articles in this category yet.」（原本整片空白）。
- 同一頁有多個表單時不再產生重複的 HTML id。
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
