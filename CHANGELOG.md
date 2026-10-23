# Guru 主題 — 版本紀錄

版本號記在 `guru-theme/style.css` 的 `Version:` 標頭（後台 外觀 → 佈景主題 會顯示），
安裝檔檔名為 `guru-theme-v<版本>-<日期>.zip`，例如 `guru-theme-v1.2.0-20260911.zip`。
壓縮檔內的資料夾一律是 `guru-theme`，所以上傳新版時 WordPress 會問「要用上傳的版本取代嗎」，選取代即可。

打包指令：`tools/package.sh`（會自動讀版本、加上今天的日期）。

---

## 1.2.8 — 2026-10-23

客戶群組 10/15～10/20 提出的問題（`~/Downloads/IMG_9932.JPG`）。

- **首頁「Your Holistic Digital Partner」移到「Our Solutions」上面，並縮小**（客戶要求）。順序變成：主視覺影片 → 客戶 logo → About → Our Solutions → Our Work。區塊上下留白 120px → 72px、標題最大 90px → 56px、數字最大 88px → 52px，桌機四個數字排成一列；區塊高度約 425px，在 1440×900 的螢幕上捲到這裡時，一個畫面能同時看到公司介紹與 Our Solutions 前三項。手機同步縮小（留白 56px、標題最大 44px），但手機畫面窄，About 本身約一個螢幕高。這是刻意偏離 Vercel 靜態版的一處。
- **首頁主視覺影片只播一次**（客戶要求）。拿掉 `loop`，播完停在最後一格。
- **頁尾拿掉「Designed with ♥ in Hong Kong」**（客戶要求）。
- **新增「選單顏色」設定**：外觀 → 自訂 → Guru site settings → Menu colours，可改選單底色、選單文字、「Contact Us」按鈕、「WhatsApp」按鈕四個顏色（電腦版上方選單與手機選單一起變）。沒改的顏色不輸出任何 CSS，網站外觀與之前完全相同。新檔 `inc/menu-colors.php`。
- **「編輯者」帳號也能進「外觀 → 自訂」**（客戶回報側邊欄沒有「外觀」）。客戶日常用的帳號是編輯者，而所有網站設定都在自訂裡。只在使用本主題時生效（不改資料庫裡的角色）；匯入工具、換主題、外掛等仍然只有管理員能用。
- **作品／Solutions 編輯畫面的「Showcase details」「Solution media & highlights」永遠展開**（客戶回報「上傳影片的欄位不見了」）。實際是欄位框被點到標題而收合，只剩一條標題列，WordPress 會記住收合狀態。現在這兩個框拿掉收合與「顯示項目設定」裡的隱藏選項，之前存下的收合／隱藏狀態也一併忽略。

## 1.2.7 — 2026-10-15

- **首頁「Our Solutions」改成滑鼠移過去就展開**（客戶要求）。原本要先點名稱展開簡介，再點「Explore →」才會到 Solutions 頁，總共點兩次。現在用滑鼠的電腦上，**移到哪一列就展開那一列的簡介**（快速掃過不會每列都打開，只開停下來的那列），**點名稱一次就直接跳到那個 solution**；展開時右側箭頭從「⌄」改成「→」，提示可以點。手機與平板沒有滑鼠移過去這個動作，維持原本做法：點一下展開簡介，再點「Explore →」前往。改動在 `assets/js/main.js` 與 `style.css`。
- **修正：手機上「Our Solutions」展開後看不到「Explore →」**。展開區高度上限是 240px，但測試站的簡介在手機寬度要 270～323px，超出的部分（包括 Explore 連結）被切掉，手機使用者無法前往 Solutions 頁；電腦 1024px 寬時也會切到一點。上限改為 480px。

## 1.2.6 — 2026-09-22

- **首頁「Our Work」位置重號**：客戶把「Test」的 Homepage position 填 1，首頁卻排在第 2 格。原因是匯入的四個作品本來就佔 1～4（MGTO × CreatorWeek = 1），兩個「1」同分時舊的排前面。修正：儲存作品時**一個數字只有一個作品**——其他佔同一數字的作品會自動清掉位置（`guru_claim_home_slot()`），並在編輯畫面上方提示「第 N 格原本是 XXX，已移出首頁」；首頁查詢加上「同分時最近更新的優先」當保險。欄位說明、README、客戶教學同步改寫（不再要客戶手動把舊作品留空）。
- 客戶教學新增 **PDF 版**（`docs/客戶教學-上架作品與調整字體.pdf`，A4 直式 8 頁，截圖內嵌，給打不開 HTML 的客戶），由 `tools/guide-pdf.mjs` 透過 headless Chrome 的 DevTools protocol 從 HTML 版列印；HTML 改了就重跑。`tools/guide-template.{html,md}` 同步到 v1.2.6。

## 1.2.5 — 2026-09-22

- **Work 頁所有卡片改為一致的 4:3**（客戶要求）。靜態版原設計每第五張卡（第 1、6、11 張）是較矮的 16:9「large」變體，客戶上傳的「Test」剛好是第 1 張，看起來比旁邊兩張矮。客戶希望所有封面一樣大、畫面取中間，因此拿掉 16:9 變體：`work.css` 移除 `.prj-card.large` 規則，`guru_work_card()` 不再輸出 `large` class，`tools/parity-check.py` 同步移除該項預期。圖片裁切由 `object-fit: cover` 取正中央，不需另外設定。這是刻意偏離 Vercel 靜態版的一處。
- **About 頁的世界地圖影片移除**（客戶要求）。辦公室區塊上方原本有一支 21:9 的世界地圖影片；`template-parts/offices.php` 不再輸出，Customizer「About page → World map video」選項移除，`about.css` 相關規則刪除，`assets/media/world-map.mp4`（1.7 MB）從主題拿掉（仍在 git 歷史）。辦公室標題與卡片之間保留原本 48px 間距。同樣是刻意偏離靜態版。
- **全站規則：上傳的封面圖／影片一律填滿框、取正中央裁切**（客戶要求）。盤點主題所有顯示上傳媒體的位置，補齊漏掉的三處：Solutions 輪播的圖片（原本只有影片有規則）、Blog 文章頁的精選圖片（改為 16:9 框）、作品單頁的封面圖（改為 16:9 框）。原本就正確的：首頁與 Work 頁卡片、首頁 hero 影片、Blog 卡片、Solutions 單支影片。刻意不裁的：燈箱與作品單頁的影片本身（要看完整影片）、Solutions 手機截圖（可上下滑的長圖）、客戶 logo（logo 不能裁）。

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
