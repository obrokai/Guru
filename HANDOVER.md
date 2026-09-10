# Guru 網站專案 — 交接文件

> 這份文件用來在**另一個 Claude Code 帳號 / 新 session** 接手本專案。
> 讀完這份文件就能掌握全部背景，不需要舊對話紀錄。
>
> 專案路徑：`/Users/mike/Guru/`
> 最後更新：2026-08-24

---

## 0. 給接手的 Claude — 先讀這段

**專案現況一句話**：Guru 廣告公司的官網已完成靜態 HTML 版（6 頁，已上線 Vercel），並已完整移植成 WordPress 自訂主題（含一鍵匯入後台內容），主題已打包待客戶上傳。

**目前最該做的事**：見 [§8 待辦事項](#8-待辦事項)。

**接手時請先執行**：

```bash
cd /Users/mike/Guru && ls -1 *.html && ls guru-theme
```

**溝通語言**：使用者以繁體中文溝通，回覆請用繁體中文。

**三條硬規則（務必遵守）**：

1. **絕不接受、不索取、不處理客戶的 WordPress 帳號密碼。** 使用者曾主動要貼帳密，前一個 session 明確拒絕過。工作流程是：本機建主題 → 打包 `.zip` → 交給使用者自己上傳。**不要登入客戶的後台。**
2. **舊的 GitHub PAT 視為已外洩憑證。** 前一個 session 曾用一組 `ghp_...` token 推送到 `https://github.com/obrokai/Guru.git`。該 token 已在對話中明文出現，**必須假設已外洩**。這份文件不含該 token。若需要推送，請使用者重新產生一組新的，並提醒他到 GitHub → Settings → Developer settings → Personal access tokens **撤銷舊的那一組**。
3. **客戶的正式站是 live 網站**（`guruonline.com.hk`）。任何動作都先在測試站 `https://www.guruonline.com.hk/prometheus/` 驗證。

---

## 1. 專案背景

| 項目 | 內容 |
|---|---|
| 客戶 | Guru（香港數位行銷代理商，2007 成立，港交所 GEM 上市，股票代號 08121） |
| 角色 | 使用者是接案方，Guru 是他的客戶；客戶還有一位「老闆」會提意見 |
| 原始交付 | 靜態 HTML 網站，6 頁，部署在 Vercel |
| 線上網址 | `https://guru-ebon-eight.vercel.app`（作品影片與 logo 都還掛在這） |
| 客戶正式站 | `https://www.guruonline.com.hk`（自架 WordPress） |
| 客戶測試站 | `https://www.guruonline.com.hk/prometheus/`（WP Staging 產生，有登入保護） |
| Git remote | `https://github.com/obrokai/Guru.git` |

**客戶的核心需求**（決定了 WordPress 化的方向）：
> 要一個後台讓客戶自己**編輯文字**和**上架 showcase 作品**。

---

## 2. 靜態網站（`*.html`）

6 個頁面，全部是手寫的單檔 HTML（CSS 內嵌在 `<style>`，JS 內嵌在 `<script>`）：

| 檔案 | 頁面 |
|---|---|
| `index.html` | 首頁（hero 影片、Our Solutions 手風琴、Our Work 四格、聯絡表單） |
| `about.html` | 關於我們（品牌故事、67 個客戶 logo 牆、7 個辦公室） |
| `services.html` | Solutions（左側固定側邊欄 + 右側 9 個服務面板，每個面板下有表單） |
| `work.html` | 作品集（13 張卡片 + 分類篩選 + 影片 modal） |
| `blog.html` | 網誌 |
| `investor.html` | 投資人關係 |

**媒體檔**（都在專案根目錄）：
- `hero-bg.mp4` (11.6 MB, 1080p)、`hero-bg-mobile.mp4` (5.5 MB) — 首頁背景影片
- `influencer-1~4.mp4`、`video-production.mp4` — 作品影片（共約 88 MB）
- `client-logos/` — 67 個客戶 logo（客戶提供的原始檔，6.0 MB）

---

## 3. 設計規範（客戶多輪回饋後定案，改動請遵守）

| 項目 | 規則 |
|---|---|
| **主色** | 品牌綠 `#58B947` — **所有標題**都用這個顏色 |
| **輔色** | 藍色作為點綴元素（hero、"Our Solutions"、"Hong Kong" 等處） |
| **字體** | 全站統一 **DM Sans**，只用大小和粗細做層次區分 |
| **標題大小** | About / Work / Solutions / Blog 各頁主標題**尺寸一致** |
| **導覽列** | 向下捲動時收起，向上捲動時出現（客戶要求瀏覽 solution 時不擋內容） |
| **Logo** | 導覽列的 GURU logo 縮到與導覽文字同大小，背景霧面膠囊高度一致 |
| **聯絡表單** | 有外框（要有「表單感」）、**沒有** Enquiry Type 欄位、只用 placeholder 不用 label、Send 按鈕要顯眼、電話用國碼下拉 + 號碼分離、**不要國旗** |
| **客戶 logo 牆** | 固定尺寸方形 canvas、排版整齊、預設灰階、hover 變彩色 |

### ⚠️ 一個容易重蹈覆轍的 CSS 陷阱

```css
/* 錯誤 —— 會讓所有子元素的 position:sticky 失效 */
html, body { overflow-x: hidden; }

/* 正確 */
html, body { overflow-x: clip; }
```

`overflow-x: hidden` 會建立一個 scroll container，導致 `services.html` 左側欄的 `position: sticky` 失效。客戶回報過「左邊欄位沒固定住」就是這個原因。**改動這兩行前請三思。**

---

## 4. WordPress 主題（主要成果）

路徑：`guru-theme/`　安裝檔：`guru-theme.zip`(17MB) / `guru-theme-slim.zip`(783KB，不含 hero 影片)

### 4.1 設計原則

- **零外掛依賴** — 作品集、服務、logo、表單、後台欄位全部內建，沒用 ACF / Elementor / Contact Form 7
- 只建議客戶另裝一個 SMTP 外掛改善信件送達率

### 4.2 檔案結構

```
guru-theme/
├── functions.php              主題啟動；資源載入（依頁面條件載入 CSS）
├── style.css                  全站共用樣式 (1556 行)
├── header.php / footer.php    導覽列（含 fallback 選單）/ 頁尾（含聯絡表單）
├── front-page.php             首頁
├── page-about.php             Template Name: About
├── page-services.php          Template Name: Solutions
├── page-investor.php          Template Name: Investor
├── archive-work.php           作品集列表
├── single-work.php            作品內頁
├── single.php / index.php     文章內頁 / 文章列表
├── inc/
│   ├── post-types.php         註冊 CPT 與分類法
│   ├── fields.php             Showcase 的後台欄位（Client / Video，含媒體選擇器）
│   ├── template-helpers.php   guru_work_card()、guru_work_filters()、Guru_Nav_Walker
│   ├── customizer.php         外觀→自訂 的所有設定
│   ├── contact-form.php       表單處理 + Enquiry 存檔
│   ├── importer.php           ★ 一鍵匯入（744 行）
│   └── seed-data.json         ★ 匯入用的內容資料
├── template-parts/
│   ├── client-wall.php        客戶 logo 牆（CPT 優先，退回主題資料夾）
│   ├── logo-ticker.php        首頁 logo 跑馬燈
│   ├── offices.php            辦公室卡片
│   ├── solution-form.php      每個 solution 下方的表單
│   └── work-modal.php         作品影片彈窗
└── assets/
    ├── css/{about,work,blog,services,investor}.css   依頁面載入
    ├── js/main.js
    └── media/  hero 影片、logo、client-logos/(67 個 webp)
```

### 4.3 自訂文章類型

| CPT | 後台名稱 | 用途 | 自訂欄位 |
|---|---|---|---|
| `work` | Showcase | 作品 | `_guru_client`（客戶名）、`_guru_video_url`（影片） |
| `solution` | Solutions | 服務項目 | 用標準的標題／摘要／內文／順序 |
| `client_logo` | Client logos | 客戶 logo | 只用精選圖片 |
| `guru_enquiry` | Enquiries | 表單來信存檔（private） | `_guru_email` 等 |

分類法：`work_category`（6 個詞彙），作品頁的篩選按鈕由它自動產生。

### 4.4 一鍵匯入（`inc/importer.php`）

後台左側選單 **Guru → Guru setup**，四個按鈕：

| 步驟 | 內容 | 結果 |
|---|---|---|
| 1. Import content | 9 Solutions + 13 Showcase + 6 分類 | ~2 秒 |
| 2. Create pages and menu | 5 個頁面、首頁/網誌頁設定、主選單 | ~2 秒 |
| 3. Import client logos | 67 個 logo 進媒體庫（選用） | ~10 秒 |
| 4. Import showcase videos | 從舊站下載 5 支影片（可分次） | 較久 |

**全部具備冪等性**：重複執行只更新、不重複建立（步驟 2 的選單會先清空再重建）。
步驟 3、4 有時間預算（20 秒 / 45 秒），會自動中斷並提示「再按一次繼續」。

匯入的內容來源是 `inc/seed-data.json`，直接編輯該檔後重跑匯入即可更新。

### 4.5 資安措施（改動時請保留）

- 表單送到 `admin-post.php`，動作 `guru_contact`
- nonce 驗證 + 蜜罐欄位 `guru_website`（機器人填了就假成功但不入庫）+ 必填驗證
- 導向網址白名單限制在自己的網域
- 後台欄位有 `wp_verify_nonce` + `current_user_can` + autosave 防護
- 匯入功能檢查 `manage_options` + `check_admin_referer`

---

## 5. 本機驗證環境（重要）

前一個 session 沒有靠「看起來對」交付，而是架了真的 WordPress 實測。**建議接手時照樣做。**

```
/tmp/wptest/          WordPress 7.1 + SQLite drop-in
                      伺服器：php -S localhost:8899 -t /tmp/wptest
                      這是拋棄式測試站，帳密是隨便設的，與客戶的站完全無關
```

啟動與部署：

```bash
php -S localhost:8899 -t /tmp/wptest
```

```bash
rm -rf /tmp/wptest/wp-content/themes/guru-theme && cp -R /Users/mike/Guru/guru-theme /tmp/wptest/wp-content/themes/
```

**注意**：`switch_theme()` 在同一個 PHP request 內不會重新載入 `functions.php`，所以「切換主題」和「執行匯入」必須拆成兩支腳本分別執行。

### 已通過的驗證（2026-08-24）

- 8 個前台網址全部 HTTP 200，`debug.log` 零主題錯誤
- 全新資料庫匯入結果精確：`9 solutions / 13 works / 6 categories / 67 logos`
- 重跑第二次全部 0 新增（冪等性確認）
- 67 個 logo 圖片全部載入成功，0 破圖
- 表單：正常送出 → `contact=sent` 且存入 Enquiries；蜜罐送出 → 假成功但不入庫
- slim 版（移除 hero 影片）首頁不輸出 `<video>`，版面不破

---

## 6. 客戶安裝流程

1. 外觀 → 佈景主題 → 上傳 `guru-theme.zip` → 啟用
   （上傳失敗就用 `guru-theme-slim.zip`，之後在 自訂 → Hero 上傳影片）
2. Guru → Guru setup → 依序按 1～4
3. 設定 → 固定網址 → 選「文章名稱」

完整說明在 `guru-theme/README.md`（繁體中文，可直接給客戶）。

---

## 7. 已知的坑 / 前人踩過的雷

| 問題 | 原因與解法 |
|---|---|
| 側邊欄 sticky 失效 | `overflow-x: hidden` → 改 `clip`（見 §3） |
| 頁尾 G logo 蓋住「NAVIGATION」 | `.ftr-logo` 寫死 `height:28px` 但圖是 88px → 移除高度限制 |
| "Our Services" 幽靈按鈕在亮色影片上看不見 | 改成白色實心膠囊 + 深色文字 |
| 首頁 hero 影片本身帶有標題文字 | 與 "AGENCY." 標題衝突 → 已移除 HTML 標題，改為全出血影片 |
| 作品影片共 88 MB | **不要打包進主題**。用匯入步驟 4 從線上站下載 |
| 客戶 logo 原始檔 6 MB | 已轉成 480px WebP → 0.5 MB 打包進主題 |
| CSS 想做 selector 去重 | **不要做**。稽核發現 54 處同選擇器不同內容的覆寫，去重會靜默改變樣式。已改用「每頁一個 CSS 檔」的做法 |

---

## 8. 待辦事項

### 高優先

- [ ] **客戶尚未提供的資料**：正式電話號碼、WhatsApp 號碼（目前是佔位的 `+852 0000 0000`）、投資人頁的真實 PDF 連結
- [ ] **上正式站前務必執行匯入步驟 4**。否則作品影片還指向 Vercel，Vercel 一關影片就掛掉
- [ ] **舊網址 → 新網址的 301 對照表**（先前答應要做，等確認正式站頁面結構）

### 中優先

- [ ] 把未提交的變更 commit（見 §9）
- [ ] 請客戶確認：WP 版本、PHP 版本、已裝外掛清單、現有頁面清單、固定網址設定

### 已完成

- [x] 靜態網站 6 頁 + 多輪客戶回饋修改
- [x] WordPress 主題全部模板
- [x] 一鍵匯入功能 + 本機實測
- [x] 兩種打包（完整版 / slim 版）
- [x] 客戶用的繁中安裝說明 `guru-theme/README.md`

---

## 9. Git 狀態

最後一次 commit：`e9f2c0d Add WordPress theme (phase 1)`

**尚未提交**（就是這次 session 做的 phase 2）：

```
修改：  guru-theme/README.md
        guru-theme/front-page.php      (hero 影片改為可選)
        guru-theme/functions.php       (依 template 載入 CSS + 掛載 importer)
        guru-theme/inc/customizer.php  (hero 影片 fallback 檢查檔案是否存在)

新增：  guru-theme/inc/importer.php
        guru-theme/inc/seed-data.json
        guru-theme/page-{about,services,investor}.php
        guru-theme/single.php  guru-theme/single-work.php
        guru-theme/template-parts/{client-wall,offices,solution-form}.php
        guru-theme/assets/css/          (5 個依頁面載入的 CSS)
        guru-theme/assets/media/client-logos/   (67 個 webp)
        guru-theme-slim.zip
```

建議的 commit 訊息：

```
Add WordPress theme phase 2: page templates and content importer
```

**推送前記得**：`.zip` 和 `.DS_Store` 應該加進 `.gitignore`，不要進版控。

---

## 10. 常用指令

啟動本機 WordPress：

```bash
php -S localhost:8899 -t /tmp/wptest
```

部署主題到本機測試站：

```bash
rm -rf /tmp/wptest/wp-content/themes/guru-theme && cp -R /Users/mike/Guru/guru-theme /tmp/wptest/wp-content/themes/
```

全主題 PHP 語法檢查：

```bash
find /Users/mike/Guru/guru-theme -name '*.php' -exec php -l {} \; | grep -v "No syntax errors"
```

重新打包兩個版本：

```bash
cd /Users/mike/Guru && find guru-theme -name '.DS_Store' -delete && rm -f guru-theme.zip guru-theme-slim.zip && zip -rq guru-theme.zip guru-theme && zip -rq guru-theme-slim.zip guru-theme -x 'guru-theme/assets/media/hero-bg.mp4' 'guru-theme/assets/media/hero-bg-mobile.mp4'
```

檢查主題錯誤：

```bash
grep -i "guru" /tmp/wptest/wp-content/debug.log
```
