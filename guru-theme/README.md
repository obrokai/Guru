# Guru WordPress 主題 — 安裝與使用說明

主題資料夾：`guru-theme`
安裝檔（檔名含版本與日期，請用最新的一份）：
`guru-theme-v1.2.0-20260911.zip`（約 21 MB，含所有影片）／
`guru-theme-v1.2.0-20260911-slim.zip`（約 3 MB，不含影片）

> 之後每次更新都會是新的檔名，例如 `guru-theme-v1.3.0-20261001.zip`。
> 壓縮檔內的資料夾都是 `guru-theme`，所以上傳新版時 WordPress 會問
> 「已安裝相同佈景主題，要以上傳的版本取代嗎？」→ **選「取代」**，不會變成兩套主題。
> 目前安裝的版本可在 **外觀 → 佈景主題** 卡片上看到。

---

## 一、安裝

1. WordPress 後台 → **外觀 → 佈景主題 → 安裝佈景主題 → 上傳佈景主題**
2. 選 `guru-theme.zip` → 安裝 → **啟用**
3. 如果上傳時出現「檔案大小超過限制」，改用 `guru-theme-slim.zip`，之後在
   **外觀 → 自訂 → Guru site settings → Homepage** 上傳首頁背景影片即可。

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
| 上架 / 修改作品 | **Showcase** → 新增項目。填標題 → 標題下方的 **Showcase details** 填 Client、Card labels、Homepage position（1～4 = 首頁「Our Work」四格，1 是大卡）、Video（影片作品用媒體庫選檔）→ **右欄「Cover image」設定封面圖** → 右欄勾分類 → 發布。**封面圖請用右欄的 Cover image，不要貼進中間的內文編輯器**（內文只會出現在作品自己的頁面） |
| 服務內容 | **Solutions** → 編輯。標題、摘要（頁面上的導言）、內文（選用）；排序用「順序」欄位。下方 **Solution media & highlights** 可設定短標籤、手機截圖／影片輪播／單支影片，以及四張重點卡 |
| 客戶 logo（About 頁客戶牆） | **Client logos** → 每則設一張 **Logo image** 即可。建議上傳 **PNG 或 JPG**（正方形、約 480px）；主機的圖片程式庫版本較舊，上傳 WebP 會被壓出色偏 |
| 首頁 logo 跑馬燈 | 預設顯示原設計的六個品牌字標。要換：在 **Client logos** 編輯畫面右側勾「**Show this logo in the homepage marquee**」（適合橫式字標，方形 logo 會太小） |
| 表單來信 | **Enquiries** → 所有送出的表單都會存檔並可下載 CSV，同時寄到聯絡信箱 |
| 網誌文章 | **文章** → 一般 WordPress 文章；文章的第一個分類決定卡片顏色 |
| 首頁文字（Solutions 導言、About 區標題／數字／段落）、首頁影片 | **外觀 → 自訂 → Guru site settings → Homepage** |
| About 頁（成立年份、故事標題、客戶牆標題、辦公室） | **外觀 → 自訂 → Guru site settings → About page**（About 頁的標題、導言與故事內文則在 **頁面 → About** 直接編輯） |
| 聯絡資訊、WhatsApp、頁尾標題 | **外觀 → 自訂 → Guru site settings → Contact details** |
| 社群連結 | **外觀 → 自訂 → Guru site settings → Social links**。三個都留空時，頁尾的「Follow Us」欄整個不顯示（不會出現空連結） |
| 投資人頁（公司名稱、簡介、上市資料、文件清單、公司治理、聯絡處、免責聲明） | **外觀 → 自訂 → Guru site settings → Investor page** |
| 網誌導言 | **外觀 → 自訂 → Guru site settings → Blog** |
| **字體大小** | **外觀 → 自訂 → Guru site settings → Typography**。四個百分比：內文、區塊標題、頁面大標題、導覽列連結（70–150%，預設 100%）。改動會套用到全站同類文字，手機版一樣有效；改回 100% 即恢復原設計 |
| 選單項目 | **外觀 → 選單 → 「Guru Primary」**（匯入步驟 2 建立的那個；導覽列只認這個選單，其他選單不會顯示） |

### 上傳圖片與影片的建議

主機的 PHP 記憶體與圖片程式庫版本都偏舊，太大的檔案會在「後續處理」階段失敗（上傳看似完成，卻沒有出現在媒體庫）。

| 類型 | 建議 |
|---|---|
| 封面圖、文章圖 | **JPG**（或 PNG），長邊 **不超過 2000px**，檔案 **2 MB 以內**。設計原檔請先縮圖再上傳 |
| 封面比例 | 任何比例都可以上傳：網站會自動**填滿框、取正中央裁切**（Work 卡片 4:3、Blog 文章頁與作品單頁 16:9）。不想被裁掉內容就直接做成該比例，主體盡量放畫面中間 |
| 客戶 logo | PNG 或 JPG，正方形約 480px |
| 作品影片 | MP4（H.264），**30 MB 以內**、1080p 以下 |

上傳失敗時，錯誤訊息會出現在畫面左下角的黑色提示框；請把整句文字抄下來或截圖給開發人員。

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

## 五、舊網址會自動轉址

舊站的頁面與網址（About Us、Our Work、Contact Us、各 Service、Portfolio Item、Last Announcement、Financial Information 等）啟用主題後會自動 301 到新頁面，不需要另外設定。投資人文件（`/investor-document/…`）與隱私權頁面保持原網址。
匯入步驟 2 會把佔住 `/about/`、`/investor/` 等網址的舊頁面改為草稿並加上 `-old` 後綴，內容不會刪除，可在 **頁面 → 草稿** 找到。

## 六、測試站與推送正式站（WPvivid）

建議用 **WPvivid Backup** 外掛建立測試站：

1. 外掛 → 安裝並啟用 WPvivid Backup → 左側 **WPvivid Backup → Staging → Create A Staging Site**
2. 在測試站（`/staging/` 或你取的名稱）安裝本主題、跑完匯入、修改內容
3. 確認無誤 → **先按 Backup 備份正式站** → 再按 **Push to Live**

推送時請**排除 `wp_guru_enquiries` 這張資料表**（表單來信）。它只在正式站產生，
排除後推送永遠不會洗掉正式站收到的來信；主題已將來信獨立存放，正是為了這個目的。

兩條紀律：**所有內容修改都在測試站做，不直接改正式站**（推送會覆蓋）；
**每次推送前先備份**。

## 七、不需要安裝任何外掛

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
