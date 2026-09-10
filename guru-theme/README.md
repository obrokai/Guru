# Guru WordPress 主題 — 安裝與使用說明

主題資料夾：`guru-theme`
安裝檔：`guru-theme.zip`（17 MB，含首頁背景影片）／ `guru-theme-slim.zip`（782 KB，不含影片）

---

## 一、安裝

1. WordPress 後台 → **外觀 → 佈景主題 → 安裝佈景主題 → 上傳佈景主題**
2. 選 `guru-theme.zip` → 安裝 → **啟用**
3. 如果上傳時出現「檔案大小超過限制」，改用 `guru-theme-slim.zip`，之後在
   **外觀 → 自訂 → Guru site settings → Hero** 上傳首頁背景影片即可。

## 二、一鍵匯入內容

啟用後，左側選單會出現 **Guru**。點進去 → **Guru setup**，依序按四個按鈕：

| 步驟 | 做什麼 | 需時 |
|---|---|---|
| 1. Import content | 建立 9 個 Solutions、13 個 Showcase、6 個作品分類 | 約 2 秒 |
| 2. Create pages and menu | 建立 Home／About／Solutions／Investor／Blog 五個頁面、設定首頁與網誌頁、建立主選單 | 約 2 秒 |
| 3. Import client logos | 把 67 個客戶 logo 匯入媒體庫 | 約 10 秒 |
| 4. Import showcase videos | 從舊站下載 5 支作品影片到本站媒體庫 | 較久，可分次執行 |

> 步驟可重複執行，**不會產生重複資料**——已存在的項目只會被更新。
> 步驟 3 是選用的：即使不執行，About 頁的客戶牆也會直接顯示主題內附的 logo。
> 步驟 4 若中途停止，再按一次按鈕即可接續。

## 三、客戶日常維護的位置

| 要改什麼 | 去哪裡 |
|---|---|
| 上架 / 修改作品 | **Showcase** → 新增項目。填標題、Client、選分類、設定精選圖片；若是影片作品，在 Video 欄位用媒體庫選檔 |
| 服務內容 | **Solutions** → 編輯。標題、摘要（頁面上的導言）、內文（重點列表）；排序用「順序」欄位 |
| 客戶 logo | **Client logos** → 每則設一張精選圖片即可 |
| 表單來信 | **Enquiries** → 所有送出的表單都會存檔，同時寄到聯絡信箱 |
| 網誌文章 | **文章** → 一般 WordPress 文章 |
| 聯絡資訊、社群連結、辦公室、投資人資料、首頁影片 | **外觀 → 自訂 → Guru site settings** |
| 選單項目 | **外觀 → 選單** |

## 四、安裝後要記得改的三件事

1. **電話與 WhatsApp**：目前是佔位號碼 `+852 0000 0000`。
   到 **外觀 → 自訂 → Guru site settings → Contact** 填入正式號碼。
2. **投資人頁的 PDF 連結**：目前是示範資料，
   在 **外觀 → 自訂 → Guru site settings → Investor** 逐行填
   `日期 | 標題 | 副標 | 英文版網址 | 中文版網址`。
3. **固定網址**：**設定 → 固定網址** 選「文章名稱」，
   Showcase 的網址才會是 `/work/項目名稱/`。

## 五、不需要安裝任何外掛

作品集、服務、客戶 logo、聯絡表單、後台欄位全部內建於主題，
沒有用到 ACF、Elementor 或任何表單外掛。

聯絡表單以 `wp_mail()` 寄信。若主機的信件常進垃圾桶，
建議另外安裝一個 SMTP 外掛（例如 WP Mail SMTP）改善送達率——這是唯一建議的外掛。

---

## 技術備註（給開發者）

- 自訂文章類型：`work`（Showcase）、`solution`、`client_logo`、`guru_enquiry`（私有）
- 分類法：`work_category`，作品頁的篩選按鈕由此產生
- 表單送到 `admin-post.php`，動作 `guru_contact`；有 nonce、蜜罐欄位與必填驗證
- 每頁 CSS 依條件載入（`assets/css/*.css`），共用樣式在 `style.css`
- 匯入資料存放於 `inc/seed-data.json`，可直接編輯後重跑匯入
