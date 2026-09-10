# Guru WordPress 主題 — 安裝與使用說明

主題資料夾：`guru-theme`
安裝檔：`guru-theme.zip`（約 21 MB，含所有影片）／ `guru-theme-slim.zip`（約 3 MB，不含影片）

---

## 一、安裝

1. WordPress 後台 → **外觀 → 佈景主題 → 安裝佈景主題 → 上傳佈景主題**
2. 選 `guru-theme.zip` → 安裝 → **啟用**
3. 如果上傳時出現「檔案大小超過限制」，改用 `guru-theme-slim.zip`，之後在
   **外觀 → 自訂 → Guru site settings → Homepage** 上傳首頁背景影片、
   **→ About page** 上傳世界地圖影片即可。

## 二、一鍵匯入內容

啟用後，左側選單會出現 **Guru**。點進去 → **Guru setup**，依序按四個按鈕：

| 步驟 | 做什麼 | 需時 |
|---|---|---|
| 1. Import content | 建立 9 個 Solutions（含每個方案的媒體區與重點卡）、13 個 Showcase、6 個作品分類、7 個網誌分類 | 約 2 秒 |
| 2. Create pages and menu | 建立 Home／About／Solutions／Investor／Blog 五個頁面（網址為 `/about/`、`/solutions/`、`/investor/`、`/blog/`），並把**首頁、網誌頁、主選單**指向這些頁面（舊主題留下的選單與首頁設定會被換掉；舊頁面本身不會被刪） | 約 2 秒 |
| 3. Import client logos | 把 67 個客戶 logo 匯入媒體庫 | 約 10 秒 |
| 4. Import showcase videos | 從舊站下載 5 支作品影片到本站媒體庫，並同步更新 Solutions 頁的影片連結 | 較久，可分次執行 |

> 步驟可重複執行，**不會產生重複資料，也不會覆蓋你改過的內容**——再按一次只會補上缺少的項目與空白欄位。
> 步驟 3 是選用的：即使不執行，About 頁的客戶牆也會直接顯示主題內附的 logo。
> 步驟 4 每按一次最多跑約 30 秒就會自動停下並顯示還剩幾支，再按一次即可接續。

## 三、客戶日常維護的位置

| 要改什麼 | 去哪裡 |
|---|---|
| 上架 / 修改作品 | **Showcase** → 新增項目。填標題、Client、選分類、設定 **Cover image**（封面圖）；若是影片作品，在 Video 欄位用媒體庫選檔。**Card labels** 是卡片上的小標籤（留空就顯示分類名稱）；**Homepage position** 填 1～4 可把作品放到首頁「Our Work」四格（1 是大卡） |
| 服務內容 | **Solutions** → 編輯。標題、摘要（頁面上的導言）、內文（選用）；排序用「順序」欄位。下方 **Solution media & highlights** 可設定短標籤、手機截圖／影片輪播／單支影片，以及四張重點卡 |
| 客戶 logo（About 頁客戶牆） | **Client logos** → 每則設一張 **Logo image** 即可 |
| 首頁 logo 跑馬燈 | 預設顯示原設計的六個品牌字標。要換：在 **Client logos** 編輯畫面右側勾「**Show this logo in the homepage marquee**」（適合橫式字標，方形 logo 會太小） |
| 表單來信 | **Enquiries** → 所有送出的表單都會存檔並可下載 CSV，同時寄到聯絡信箱 |
| 網誌文章 | **文章** → 一般 WordPress 文章；文章的第一個分類決定卡片顏色 |
| 首頁文字（Solutions 導言、About 區標題／數字／段落）、首頁影片 | **外觀 → 自訂 → Guru site settings → Homepage** |
| About 頁（成立年份、故事標題、客戶牆標題、辦公室、世界地圖影片） | **外觀 → 自訂 → Guru site settings → About page**（About 頁的標題、導言與故事內文則在 **頁面 → About** 直接編輯） |
| 聯絡資訊、WhatsApp、頁尾標題 | **外觀 → 自訂 → Guru site settings → Contact details** |
| 社群連結 | **外觀 → 自訂 → Guru site settings → Social links** |
| 投資人頁（公司名稱、簡介、上市資料、文件清單、公司治理、聯絡處、免責聲明） | **外觀 → 自訂 → Guru site settings → Investor page** |
| 網誌導言 | **外觀 → 自訂 → Guru site settings → Blog** |
| 選單項目 | **外觀 → 選單** |

## 四、安裝後要記得改的三件事

1. **WhatsApp**：目前是佔位連結 `https://wa.me/85200000000`。
   到 **外觀 → 自訂 → Guru site settings → Contact details** 填入正式號碼
   （格式 `https://wa.me/852XXXXXXXX`，國碼後直接接號碼，不加空格）。
2. **投資人頁的 PDF 連結**：目前是示範資料，
   在 **外觀 → 自訂 → Guru site settings → Investor page** 逐行填
   `日期 | 標題 | 副標 | 英文版網址 | 中文版網址`。
   （下一階段會改為直接沿用現有網站的 1,045 筆「Investor Doc」資料，屆時此欄位停用。）
3. **固定網址**：**設定 → 固定網址** 選「文章名稱」，
   Showcase 的網址才會是 `/work/項目名稱/`。

## 五、測試站與推送正式站（WPvivid）

建議用 **WPvivid Backup** 外掛建立測試站：

1. 外掛 → 安裝並啟用 WPvivid Backup → 左側 **WPvivid Backup → Staging → Create A Staging Site**
2. 在測試站（`/staging/` 或你取的名稱）安裝本主題、跑完匯入、修改內容
3. 確認無誤 → **先按 Backup 備份正式站** → 再按 **Push to Live**

推送時請**排除 `wp_guru_enquiries` 這張資料表**（表單來信）。它只在正式站產生，
排除後推送永遠不會洗掉正式站收到的來信；主題已將來信獨立存放，正是為了這個目的。

兩條紀律：**所有內容修改都在測試站做，不直接改正式站**（推送會覆蓋）；
**每次推送前先備份**。

## 六、不需要安裝任何外掛

作品集、服務、客戶 logo、聯絡表單、後台欄位全部內建於主題，
沒有用到 ACF、Elementor 或任何表單外掛。

聯絡表單以 `wp_mail()` 寄信。現有網站已安裝 **YaySMTP（Amazon SES）**，
請保留它，信件就會經由 SES 送出。

---

## 技術備註（給開發者）

- 自訂文章類型：`work`（Showcase）、`solution`、`client_logo`
- 分類法：`work_category`，作品頁的篩選按鈕由此產生
- Showcase 自訂欄位：`_guru_client`、`_guru_video_url`、`_guru_tags`（卡片標籤陣列）、`_guru_home_order`（首頁位置 1–4）
- Solution 自訂欄位：`_guru_panel_tag`（短標籤）、`_guru_media_type`（phones / carousel / video）、`_guru_media_label`、`_guru_media_items`（陣列：src / caption / alt）、`_guru_highlights`（陣列：icon / title / desc）
- 匯入器在每筆匯入的文章上記 `_guru_seed_key`（身分）與 `_guru_seeded_keys`（已填過的欄位），所以重跑只補缺、不覆蓋；`solution` 沒有單篇網址（`publicly_queryable => false`）
- 表單送到 `admin-post.php`，動作 `guru_contact`；有 nonce、蜜罐欄位、必填驗證與導向白名單
- 來信存於獨立資料表 `{prefix}guru_enquiries`（`inc/enquiries.php`，啟用主題時以 dbDelta 建立），後台 **Enquiries** 提供列表、刪除與 CSV 匯出
- 所有 `get_theme_mod()` 讀取的設定都在 `inc/customizer.php` 註冊，預設值集中於 `guru_default()`
- 每頁 CSS 依條件載入（`assets/css/*.css`），共用樣式在 `style.css`；Work 頁與 Blog 頁使用精簡版頁尾、Investor 頁使用 `footer-minimal.php`，與靜態站一致
- 匯入資料存放於 `inc/seed-data.json`，可直接編輯後重跑匯入；`tools/` 內有本機驗證用的比對腳本
