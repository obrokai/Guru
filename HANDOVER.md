# Guru 網站專案 — 交接文件

> 這份文件用來在**另一個 Claude Code 帳號 / 新 session** 接手本專案。
> 讀完這份文件就能掌握全部背景，不需要舊對話紀錄。
>
> 專案路徑：`/Users/mike/Guru/`
> 最後更新：2026-10-23（v1.2.8；各版細節見 `CHANGELOG.md`，客戶現況與測試站操作紀錄見主資料夾 `HANDOFF-20261015.md`）

---

## 0. 給接手的 Claude — 先讀這段

**專案現況一句話**：Guru 官網的靜態版（6 頁，Vercel）已完整移植成 WordPress 自訂主題；phase 3 補齊了與靜態站的所有視覺差異、修了多個功能 bug、把表單來信改為獨立資料表（push-safe），並在本機真的 WordPress 上通過自動比對驗證。**尚未上線。** 上線前還有一個必做項目：投資人文件（1,045 筆）的模板，見 §8。

**接手時請先執行**：

```bash
cd /Users/mike/Guru && git log --oneline -5 && ls guru-theme tools
```

**溝通語言**：使用者以繁體中文溝通，回覆請用繁體中文。

**三條硬規則（務必遵守）**：

1. **絕不接受、不索取、不處理客戶的 WordPress 帳號密碼。** 使用者曾主動要貼帳密，前兩個 session 都明確拒絕過。工作流程是：本機建主題 → 打包 `.zip` → 交給使用者自己上傳。**不要登入客戶的後台。** 使用者自己有後台權限，需要後台資訊時請他截圖放到 `~/Desktop/guru/`，用 Read 工具看圖。
2. **舊的 GitHub PAT 視為已外洩憑證。** 曾有一組 `ghp_...` token 在對話中明文出現過。這份文件不含該 token。若需要推送，請使用者自己執行 `git push`，並提醒他到 GitHub → Settings → Developer settings → Personal access tokens **撤銷舊的那一組**。
3. **客戶的正式站是 live 網站**（`guruonline.com.hk`，港交所 GEM 上市公司 08121 的官網，投資人區有監管義務）。任何動作都先在測試站驗證。舊的 `/prometheus/` 測試站已決定不留；新的測試站流程見 §6。

---

## 1. 專案背景

| 項目 | 內容 |
|---|---|
| 客戶 | Guru（香港數位行銷代理商，2007 成立，港交所 GEM 上市，股票代號 08121，上市主體 Guoen (Holdings) Limited） |
| 角色 | 使用者是接案方，Guru 是他的客戶；客戶還有一位「老闆」會提意見 |
| 原始交付 | 靜態 HTML 網站，6 頁，部署在 Vercel |
| 線上網址 | `https://guru-ebon-eight.vercel.app`（作品影片與 logo 都還掛在這；**視覺比對的基準**） |
| 客戶正式站 | `https://www.guruonline.com.hk`（自架 WordPress） |
| Git remote | `https://github.com/obrokai/Guru.git` |

**客戶的核心需求**：後台讓客戶自己**編輯文字**、**上架 showcase 作品**，並希望有**測試站可以編輯後 push 到正式站**。

### 1.1 正式站現況（2026-09-10 盤點，由公開資料與使用者提供的後台截圖得知）

| 項目 | 發現 |
|---|---|
| 伺服器 | AWS EC2，Ubuntu 16.04（kernel 4.4）、nginx 1.10.3、**PHP 7.4.13**（fpm）、MySQL 5.7.33 —— 全部 EOL，但仍在跑 |
| PHP 限制 | `max_execution_time` **30 s**、memory 128M（後台 256M）、`upload_max_filesize` 4096M |
| 資料庫 | 名稱 `guru`、前綴 `wp_`、**以 root 帳號連線**（應告知客戶改專用帳號） |
| WordPress | 7.1（與本機測試相同） |
| 啟用外掛（7） | ACF 6.8、ACF Field for CF7、Contact Form 7、Visual Composer、**Pods**、**YaySMTP（Amazon SES，已滿足寄信需求，勿刪）**、AI Provider for Anthropic |
| 停用外掛（7） | Elated Core / Instagram / Twitter、WPBakery、Slider Revolution、Hello Dolly、Akismet —— 切主題後可全刪 |
| 舊主題 | 自訂主題「Guru Online」（Elated 系）；使用 Visual Composer 蓋頁面 |
| 內容 | 11 頁、1 篇文章、14 `portfolio-item`、12 `service`、**1,045 筆 `investor_document`**（中英各約半，分類法 `document_category`：last-announcement ≈880 / financial-information ≈180），每月持續新增 |
| investor_document 欄位（ACF） | `date_of_publish`（日期選擇器，回傳 `Y.m.d`，DB 存 `Ymd`）、`document`（檔案，回傳 URL，DB 存附件 ID）。無語言欄位，中英靠標題與檔名 `_c` 後綴分辨。PDF 在 `uploads/YYYY/MM/<港交所編號>[_c].pdf` |
| CPT 註冊者 | **未確認**：非 ACF（ACF 註冊 CPT = 0）、Elated Core 停用，最可能是 Pods。使用者可到 Pods Admin → Edit Pods 確認。新主題應以 `! post_type_exists()` 防禦式註冊，兩種情況都安全 |
| 備份 | 正式站**沒有備份外掛**。WP Staging 已被使用者刪除；使用者已安裝 **WPvivid** |
| 舊網址 | 1,088 個網址可由 `https://www.guruonline.com.hk/sitemap.xml`（WP 核心 sitemapindex）重抓，作 301 對照表用 |

---

## 2. 靜態網站（`*.html`）

6 個手寫單檔 HTML（CSS / JS 內嵌）：`index.html`、`about.html`、`services.html`、`work.html`、`blog.html`、`investor.html`。媒體檔在專案根目錄：`hero-bg.mp4`、`hero-bg-mobile.mp4`、`world-map.mp4`、`influencer-1~4.mp4`、`video-production.mp4`、`china-digital-1~3.png`、`client-logos/`（67 個）。

**Vercel 上的版本就是客戶已認可的視覺基準。** 主題的目標是每一頁與它完全一致（見 §5 的比對工具）。

---

## 3. 設計規範（客戶多輪回饋後定案，改動請遵守）

| 項目 | 規則 |
|---|---|
| **主色** | 品牌綠 `#58B947` — **所有標題**都用這個顏色 |
| **輔色** | 藍色作為點綴元素 |
| **字體** | 全站統一 **DM Sans**，只用大小和粗細做層次區分 |
| **標題大小** | About / Work / Solutions / Blog 各頁主標題**尺寸一致** |
| **導覽列** | 向下捲動時收起，向上捲動時出現 |
| **聯絡表單** | 有外框、**沒有** Enquiry Type 欄位、只用 placeholder、Send 按鈕要顯眼、電話用國碼下拉 + 號碼分離、**不要國旗** |
| **客戶 logo 牆** | 固定尺寸方形 canvas、預設灰階、hover 變彩色 |
| **頁尾** | 首頁 / About / Solutions 是完整表單頁尾；**Work 與 Blog 是精簡 CTA 頁尾**；**Investor 頁沒有站頁尾**，以免責聲明列結束 —— 這些都與靜態站一致 |

### ⚠️ CSS 陷阱

`html, body { overflow-x: hidden }` 會讓子元素的 `position: sticky` 失效。靜態站與主題的做法：全站 `hidden`，只有 Solutions 頁的 `services.css` 覆蓋為 `clip`（該頁有 sticky 側欄）。**不要「順手」改成全站 clip**，也不要在其他頁加 sticky 元素。

---

## 4. WordPress 主題（主要成果）

路徑：`guru-theme/`　安裝檔：`guru-theme-v<版本>-<YYYYMMDD>.zip`（約 21 MB）/ `…-slim.zip`（約 3 MB，不含三支影片），目前為 **v1.2.6 / 2026-09-22**。

**版本規則**（2026-09-11 起）：版本號寫在 `guru-theme/style.css` 的 `Version:` 標頭，`functions.php` 的 `GURU_VERSION` 要跟著改；打包用 `tools/package.sh`，檔名自動帶版本與日期。**壓縮檔內的資料夾一律是 `guru-theme`** —— WordPress 以資料夾名認主題，改了就會變成第二套主題而不是更新。每次改動請更新 `CHANGELOG.md`。

### 4.1 設計原則

- **零外掛依賴** — 作品集、服務、logo、表單、來信存檔、後台欄位全部內建
- **與靜態站逐頁一致** — 每頁的 class、結構、JS 行為都以靜態站為準（用 `tools/parity-check.py` 驗證）
- **push-safe** — 正式站上會自己產生的資料（表單來信）不放在 `wp_posts`，而放獨立資料表，測試站 push 到正式站時排除它即可

### 4.2 檔案結構

```
guru-theme/
├── functions.php              主題啟動；依頁面條件載入 CSS；require inc/*
├── style.css                  全站共用樣式（含首頁 About 區、hw-vmodal）
├── header.php / footer.php    導覽列 / 頁尾（完整表單版 ＋ Work/Blog 精簡版）
├── footer-minimal.php         Investor 頁用（只關閉文件）
├── front-page.php             首頁：hero → logo 跑馬燈 → Solutions 手風琴 → Our Work 四格 → About 統計區
├── page-about.php             Template Name: About
├── page-services.php          Template Name: Solutions（側欄 + 9 面板，含媒體區與重點卡）
├── page-investor.php          Template Name: Investor（照靜態站結構；資料暫由 Customizer 供應，見 §8）
├── archive-work.php           作品集列表（篩選鈕 + 卡片 + 靜態站的 vmodal）
├── taxonomy-work_category.php 作品分類頁（沿用 archive-work.php）
├── page.php                   ★ 一般頁面（法律頁、客戶新建的頁面）—— 沒有它，這些頁會掉到 index.php 變成無樣式的列表
├── single-work.php / single.php / index.php（網誌列表，含分類篩選鈕；也服務搜尋與 404）
├── inc/
│   ├── post-types.php         work / solution / client_logo 與 work_category
│   ├── fields.php             後台欄位：Showcase（Client、Video、Homepage position）、Solution（媒體區、重點卡）
│   ├── template-helpers.php   guru_page_url / guru_nav_items / guru_home_works / guru_work_card / guru_solution_media / Guru_Nav_Walker
│   ├── customizer.php         ★ 所有 theme_mod 都在這裡註冊；預設值集中於 guru_default()；guru_mod() / guru_pipe_rows() 輔助
│   ├── enquiries.php          ★ 來信資料表 {prefix}guru_enquiries、後台 Enquiries 列表 / 刪除 / CSV
│   ├── contact-form.php       表單處理（寫入資料表 + wp_mail）
│   ├── typography.php         ★ Customizer「Typography」四個縮放百分比 → :root CSS 變數；搭配 assets/css/type/*.css（由 tools/build-typography.py 從主題 CSS 自動產生，改 CSS 後要重跑）
│   ├── media.php              上傳保護：放寬 set_time_limit、停產 1536/2048 尺寸（主機 30 s 上限）
│   ├── importer.php           ★ 一鍵匯入（含 solution 媒體、首頁四格、網誌分類；步驟 4 在 30 s 限制下可分次）
│   ├── redirects.php          ★ 舊網址 301：about-us / our-work / contact-us / service/* / portfolio-item/* / last-announcement 等 → 新頁；investor_document 與法律頁不動
│   └── seed-data.json         ★ 匯入資料（solutions 含 media/highlights；works 含 home 1–4；blog_categories）
├── template-parts/
│   ├── client-wall.php / logo-ticker.php / offices.php（世界地圖影片已於 v1.2.5 依客戶要求移除）
│   ├── solution-form.php      每個 solution 下方的表單
│   ├── work-modal.php         首頁用 lightbox（hw-vmodal，style.css）
│   └── work-modal-archive.php Work 頁用 lightbox（vmodal，work.css）—— 與靜態站兩種設計一致
└── assets/
    ├── css/{about,work,blog,services,investor}.css   依頁面載入
    ├── js/main.js             所有前端行為（modal 控制器、Work 篩選、Solutions 面板/輪播/手機、Blog 篩選、計數動畫…）
    └── media/  hero 影片、logo、client-logos/（67 webp）、solutions/（3 張手機截圖）
```

### 4.3 自訂文章類型與欄位

| CPT | 後台名稱 | 自訂欄位 |
|---|---|---|
| `work` | Showcase | `_guru_client`、`_guru_video_url`、`_guru_tags`（卡片標籤；空則顯示分類名）、`_guru_home_order`（首頁位置 1–4；空的位置由清單前幾筆補上） |
| `solution` | Solutions | `_guru_panel_tag`（面板短標籤）、`_guru_media_type`（phones / carousel / video）、`_guru_media_label`、`_guru_media_items`（陣列 src/caption/alt）、`_guru_highlights`（陣列 icon/title/desc）。無單篇網址 |
| `client_logo` | Client logos | 精選圖片（Logo image）；`_guru_in_ticker`＝1 才進首頁跑馬燈，否則跑馬燈顯示靜態站的六個字標（方形客戶牆 logo 在 34px 高會看不見，staging 上實際發生過） |

來信不再是 CPT，在 `{prefix}guru_enquiries` 資料表；舊 `guru_enquiry` 文章會在第一次進後台時自動搬進資料表。

### 4.4 Customizer（外觀 → 自訂 → Guru site settings）

| 區塊 | 設定 |
|---|---|
| Contact details | Email、電話、WhatsApp、城市、表單頁尾標題、精簡頁尾標題與按鈕 |
| Social links | IG / LinkedIn / FB |
| Menu colours（v1.2.8） | 選單底色、文字、Contact Us、WhatsApp 四色；只輸出與預設不同的顏色（`inc/menu-colors.php`）。同檔讓「編輯者」可進外觀 → 自訂 |
| Homepage | Solutions 導言、About 區標題 / 統計（`數字 | 後綴 | 標籤`）/ 段落 / 按鈕、hero 影片（桌機、手機） |
| About page | Founded、Credential、故事標題、客戶牆標題、辦公室標題、辦公室清單 |
| Blog | 導言 |
| Typography | 內文／區塊標題／頁面大標題／導覽列連結 四個百分比（70–150，預設 100）。分組規則在 `tools/build-typography.py` 的 EXCLUDE/NAV/HERO/HEADING/BODY 正規式；標籤、按鈕、表單欄位、膠囊一律不縮放 |
| Investor page | 公司名稱、簡介、股票資料、文件清單、公告連結、公司治理文件、股價 / 舊 IR 連結、聯絡處、免責聲明 |

### 4.5 一鍵匯入（Guru → Guru setup）

四個按鈕同 README。**冪等且不破壞**：每筆匯入的文章都記 `_guru_seed_key`（用它而不是標題來找回，改名也不會重複建立）與 `_guru_seeded_keys`（哪些欄位已填過）。重跑只建立缺少的項目、只填「從未填過且目前為空」的欄位；不改既有文章的內文／狀態／排序，不刪選單項目（只補缺的五個連結），頁面 slug 只在仍是自動產生值時修正。**例外**：步驟 2 每次都會把首頁、網誌頁、主選單位置指向匯入的頁面／Primary 選單 —— 因為 WordPress 切主題時會自動把舊主題的選單接到新位置、舊的 VC 首頁也還掛著（在 staging 上實際發生過）。**網址衝突**：舊站有 slug 為 `investor` 的頁面，匯入的 Investor 頁被擠成 `investor-2`，而舊頁又因 slug 與 `page-investor.php` 同名被套上我們的模板。步驟 2 現在會把佔住 `home/about/solutions/investor/blog` 的舊頁改成 `<slug>-old` 並轉為草稿（記 `_guru_replaced_by`），讓匯入的頁面接手網址；頁面的標題比對只認「內容為空或已用我們模板」的頁，不會誤認舊站同名頁。步驟 3 先 sideload 再建文章，失敗不留空殼。步驟 4：嘗試 `set_time_limit(300)`，依實際 `max_execution_time` 計算預算（30 s 主機 → 約 18 s；放寬後 30 s），下載逾時 ≤ 25 s（PHP 放寬後 ≤ 55 s，避開常見 60 s proxy timeout）；下載完成後同步改寫 solution 媒體中的同名影片 URL。匯入訊息用 transient 傳遞，不從 URL 反射文字。

### 4.6 資安措施（改動時請保留）

- 表單：nonce、蜜罐 `guru_website`、必填驗證、導向白名單（只回本站）
- 後台欄位：`wp_verify_nonce` + `current_user_can` + autosave 防護
- 匯入：`manage_options` + `check_admin_referer`
- Enquiries：`edit_others_posts`、刪除與 CSV 匯出都有 nonce；CSV 對 `=+-@` 開頭的值加 `'` 防公式注入

---

## 5. 本機驗證環境與工具（重要）

```bash
tools/local-wp.sh            # 建 ~/wptest（WP 7.1 + SQLite）、裝主題、跑匯入
tools/local-wp.sh serve      # http://localhost:8899（admin / localpass）
tools/local-wp.sh deploy     # 改完程式後重新複製主題
```

驗證腳本（都在 `tools/`）：

| 腳本 | 做什麼 |
|---|---|
| `parity-check.py --static-dir <抓下來的 vercel html> --local http://localhost:8899` | 六頁逐一比對：靜態頁用到的每個 class 是否在主題 CSS、是否出現在渲染 HTML；加上每頁的結構斷言（幾張卡、幾個面板…）。**目標 TOTAL GAPS: 0** |
| `test-form.py` | 表單五種情境（正常 / 蜜罐 / 無效 email / 錯 nonce / 站外導向）→ 檢查資料表 |
| `router.php` | php -S 用的 pretty permalink 路由 |

phase 3 的驗證結果（2026-09-10）：六頁 HTTP 200、`debug.log` 零錯誤、parity TOTAL GAPS 0（唯一「缺」的 `vmodal-tag` 是 JS 產生的）、表單五情境全過、後台八個畫面零 PHP 錯誤、匯入重跑兩次 0 新增且不改動既有內容。另外跑了一輪六個面向（安全、PHP/WP 正確性、靜態站對齊、JS、匯入器、設定/文件）的對抗式程式審查，約 45 個發現中確認並修正了 30 多項（蜜罐未隱藏、Customizer 影片 sanitizer、來信表存在性、匯入重跑覆蓋內容、Work 頁 tags、Solutions 表單國碼、精簡頁尾回饋訊息、404/搜尋頁樣式、單篇文章／作品 CSS 等）。

**注意**：
- `switch_theme()` 與匯入必須在**不同的 PHP process** 執行（functions.php 不會重新載入）。
- php -S 不支援 HTTP Range，headless Chrome 載入本機大影片會卡住；要截圖比對時把 hero 影片設定暫時指到 Vercel（或改用 `tools/shoot-url.mjs` 走 DevTools protocol，不會卡）。
- 原本放在 `/tmp` 的測試站會被系統清掉，所以改放 `~/wptest`。

---

## 6. 測試站與上線流程（使用者操作，Claude 只準備 zip 與清單）

**測試站：WPvivid Backup（免費版）**。使用者已安裝。流程見 `guru-theme/README.md` §五。重點：
1. WPvivid → Staging → Create A Staging Site
2. 在測試站：上傳 zip → 啟用 → Guru setup 1～4 → Customizer 填值 → 逐頁比對 Vercel
3. **Backup 正式站** → Push to Live，**排除 `wp_guru_enquiries`**
4. 上線後：舊頁面下架並 301、停用 VC / CF7 / Elated 等、刪 WP Staging 殘留（`wpstg0_*` 資料表、`/prometheus/` 資料夾）

**紀律**：所有內容修改都在測試站做；push 前先備份。

---

## 7. 已知的坑 / 前人踩過的雷

| 問題 | 原因與解法 |
|---|---|
| 側邊欄 sticky 失效 | `overflow-x: hidden` vs `clip`（§3） |
| 匯入建立的頁面 slug 變成 `about-guru`、`investor-relations` | 匯入器現在明確設 `post_name`（about / solutions / investor / blog）；已存在且未被改過的自動 slug 會被修正 |
| 行動版選單 / 頁尾連結 404 | 以前用寫死的 slug 陣列找頁面；現在 `guru_nav_items()` 依 slug → 模板 → fallback 三層解析 |
| 啟用後導覽列顯示舊站選單（Home / About Us / Service / Contact Us…） | WordPress 切主題會把舊選單接到新位置。主題選單改名「Guru Primary」並以 `guru_menu_id` option 追蹤；`header.php` 只在位置指向該選單時才用 WP 選單，否則用內建五連結（`guru_use_wp_menu()`） |
| Work 頁卡片沒樣式、篩選鈕沒反應、點卡片沒 modal | phase 2 的卡片 markup（`.prj-media`）沒有 CSS，且 main.js 缺篩選與 modal 綁定；phase 3 改回靜態站 markup 並補 JS |
| Solutions 頁導言重複出現 | seed 的 content 與 excerpt 相同；現在 content 留空，模板只在有內文時輸出 |
| 步驟 4 在 30 s 主機被砍 | 見 §4.5 |
| 作品影片共 88 MB | 不要打包進主題，用步驟 4 下載 |
| CSS 想做 selector 去重 | **不要做**。54 處同選擇器不同內容的覆寫，去重會靜默改變樣式 |
| grep `add_setting(` 找不到設定 | customizer.php 用迴圈註冊（`$id` 變數），要看 `guru_default()` 的清單 |
| **卡片封面圖撐壞 Work 頁 grid** | 客戶第一次上傳封面圖才暴露：`.prj-img > img` 沒有 absolute/cover 規則，1024px 原圖的 min-content 把 `1fr` 欄位撐開，整頁變「一大兩小」。靜態站卡片只有色塊與影片所以從未需要。修法：`.prj-img > img` 與 `.wrk-card-img > img` 套用與影片相同的填滿規則，卡片 `min-width:0`。**教訓：任何 grid/flex 內放 `<img>` 的地方，都要有 width/object-fit 規則，不能靠靜態站沒出現過就略過** |
| **全站封面規則（v1.2.5，客戶指定）** | 上傳的封面圖／影片一律 `object-fit: cover`、取正中央：首頁與 Work 卡片（4:3）、Blog 卡片與文章頁精選圖（16:9）、作品單頁封面圖（16:9）、Solutions 輪播與單支影片（16:9）、首頁 hero。**刻意不裁**：燈箱與作品單頁的影片本身（contain／自然比例）、Solutions 手機截圖（可捲動長圖）、客戶 logo（contain）。新增任何顯示上傳媒體的位置時照這條走 |
| **首頁四格位置重號（v1.2.6）** | 匯入的四個作品預設佔 `_guru_home_order` 1～4；客戶把新作品填 1 就跟 CreatorWeek 同分，`meta_value_num ASC` 同分時舊的排前面，新作品掉到第 2 格。修法：儲存時 `guru_claim_home_slot()` 清掉其他同號作品的位置 + transient 提示；查詢加 `modified DESC` 當第二排序。**教訓：任何「填數字排位置」的欄位都要處理重號，不能假設客戶會先去清舊的** |
| **Typography 1.2.1/1.2.2 讓全站標題縮小** | `calc(… * var(--guru-fs-x))` 在變數未定義時整條失效，font-size 退回繼承值。class 比對抓不到這種錯，是拍 Customizer 預覽截圖才看見。修法：產生器一律輸出 `var(--guru-fs-x, 1)`；`tools/shoot-admin.mjs` 現在會在 100% 時斷言 `.abt-hero-title` 算出的字級 ≥ 50px。**改主題 CSS 後要重跑 `tools/build-typography.py`** |
| 客戶上傳大圖失敗「無法從伺服器取得有效的回應」 | 主機 PHP 30 s 上限，產縮圖超時 → `inc/media.php`；nginx 已確認放行到 60 MB、REST 正常，問題在 PHP 處理階段 |
| 客戶找不到 Showcase 欄位、把圖貼進內文 | 區塊編輯器把 meta box 擠到最底、Cover image 藏側欄 → `work`/`solution`/`client_logo` 改傳統編輯器（`use_block_editor_for_post_type`） |
| 蜜罐欄位「Website」看得到 | phase 2 沒有 `.cf-hp` 的 CSS，真人填了會被當機器人丟掉；phase 3 在 style.css 加上 off-screen 規則 |
| Customizer 影片選擇器存成 `http://123` | `WP_Customize_Media_Control` 存的是附件 ID，sanitizer 要用 `absint` 不是 `esc_url_raw` |
| 來信資料表不存在時靜默丟失 | push 排除該表但帶了 `wp_options` 的版本號 → 用 `SHOW TABLES LIKE` 檢查存在，缺就重建；insert 失敗會重建再試一次；舊 CPT 遷移只在寫入成功後才刪原文 |
| headless Chrome 對 localhost 卡住 | php -S 無 Range 支援 + Chrome 對本機 HTTP 的行為；截圖改用 file:// 快照（見 §5） |
| 客戶說「側邊欄沒有外觀」（v1.2.8） | 客戶日常帳號是「編輯者」，WordPress 不給編輯者 `edit_theme_options`。主題以 `user_has_cap` 執行時補上（不寫進角色，換主題即失效）；匯入仍要 `manage_options` |
| 客戶說「上傳影片的欄位不見了」（v1.2.8） | 「Showcase details」框被點到標題收合，只剩一條標題列，WordPress 依帳號記住。主題現在用 `postbox_classes_*` 與 `hidden_meta_boxes` 篩選讓 Guru 的框永遠展開、不能隱藏，並隱藏收合按鈕 |
| 這台 Mac 沒有 `timeout`；`chrome --screenshot` 會卡 | 截圖一律走 `tools/chrome-cdp.sh` + DevTools protocol（照 `shoot-admin.mjs` 的寫法），首頁先存成 `~/wptest/_snap_home.html` 再用 file:// 開 |
| Solutions 單篇網址 `/solutions/<slug>/` 出現在 sitemap 且無樣式 | `solution` CPT 改為 `publicly_queryable => false`、`rewrite => false` |
| About 客戶牆 logo 顏色全錯（藍變綠、有光暈） | 主機 ImageMagick 6.8.9 重新編碼 WebP 會壞掉色彩通道，WP 自動產生的 `-300x300.webp` 縮圖全毀。客戶牆與跑馬燈改用 `full` 原檔（480px、~7 KB），匯入 logo 時停用縮圖產生。客戶自行上傳的圖建議 PNG/JPG |

---

## 8. 待辦事項

### 上線前必做

- [ ] **投資人文件模板（第二批）**：正式站有 1,045 筆 `investor_document`。目前 `page-investor.php` 的文件清單來自 Customizer textarea（示範資料）。需要：以 `! post_type_exists()` 防禦式註冊 `investor_document` + `document_category`（slug 與舊站相同）；在 Investor 頁以真資料取代 textarea（分類 tab、分頁 20 筆、日期倒序、中英配對用同日 + 檔名 `_c`）；後台新增流程沿用 ACF 欄位或主題自建 meta box（讀 `date_of_publish` 的 `Ymd` 與 `document` 的附件 ID，不依賴 ACF）。**強烈建議先請使用者從正式站 工具 → 匯出 拿一份 XML 匯進本機，拿真資料測。** 同時要跟客戶談：這是原範圍外的追加開發。
- [x] **301 對照表**（`inc/redirects.php`）：`/about-us/`→About、`/our-work/`與`/portfolio-item/*`→Work、`/contact-us/`→首頁表單、`/service/<x>/`→Solutions 對應面板（12 個舊 service 的對應表在 `guru_legacy_service_map()`）、`/last-announcement/`、`/financial-information/`、`/document-category/*`→Investor、`/blog/digital-marketing-solution/`→Blog、`/home-new/`、`/test-chatbot/`→首頁、`/d-bizsolution/`、`/global-marketing-cn/`→Solutions。`/investor-document/*` 與 `/privacy-policy/`、`/fanalytics-privacy/` 保留（法律頁改由 `page.php` 呈現，舊 page-builder 標籤會被清掉）。第二批做投資人分類頁時，把 last-announcement / financial-information 改指到對應分類。
- [ ] **上正式站前務必執行匯入步驟 4**，否則作品與 Solutions 影片還指向 Vercel。
- [ ] 請客戶提供：正式 WhatsApp 號碼；確認 Pods 是否註冊 investor_document；確認是否接受刪除停用外掛與 WP Staging 殘留。
- [ ] 上線當天：WPvivid Backup → Push（排除 `wp_guru_enquiries`）→ 固定網址選「文章名稱」→ 逐頁檢查。

### 建議告知客戶（非本專案範圍）

- 伺服器 Ubuntu 16.04 / PHP 7.4 / MySQL 5.7 全部 EOL
- WordPress 以 MySQL root 帳號連線
- 54 則待審留言多為垃圾，建議關閉留言功能

### 已完成

- [x] 靜態網站 6 頁 + 多輪客戶回饋修改
- [x] WordPress 主題全部模板（phase 1–2）
- [x] 一鍵匯入 + 本機實測（phase 2）
- [x] **phase 3（2026-09-10）**：與 Vercel 六頁的視覺 / 結構 / JS 完全對齊（首頁 About 區、Solutions 媒體與重點卡、About 地圖、Blog 篩選、Investor 頁結構、Work 卡片與 modal、精簡頁尾）；Customizer 補齊全部設定；來信獨立資料表 + Enquiries 後台 + CSV；匯入器修正（30 s、slug、solution 媒體、首頁四格）；行動版選單 404 修正；`tools/` 驗證腳本

---

## 9. Git 狀態

所有主題工作都在分支 `worktree-theme-parity`（工作資料夾 `.claude/worktrees/theme-parity`，已鎖定）。**2026-10-14 起已 push 到 `origin/worktree-theme-parity`，尚未合併到 main**；最新是 v1.2.8。查看這個資料夾請用 `git -C` 或絕對路徑，**不要在 shell 裡 `cd` 進去**（10-14 曾因此被系統自動刪除，後來從 git 救回）。`.gitignore` 已排除兩個 zip 與 `.DS_Store`。

重新打包：

```bash
tools/package.sh            # 讀 style.css 的版本，輸出 guru-theme-v<版本>-<今天>.zip 與 -slim.zip
```

全主題 PHP 語法檢查：

```bash
find guru-theme -name '*.php' -exec php -l {} \; | grep -v "No syntax errors"
```

---

## 10. 其他檔案

- `SKILL.md`：與本專案無關的通用「frontend-design」skill 文件（鼓勵大膽選字體等）。**Guru 的設計已由客戶拍板，以 §3 為準，不要拿它重做既有頁面。**
