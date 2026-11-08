# Guru 主題 v1.2.12 更新清單

**版本**：1.2.12　**日期**：2026-11-08
**安裝檔**：`guru-theme-v1.2.12-20261108.zip`／`guru-theme-v1.2.12-20261108-slim.zip`（不含影片）

測試站原本是 1.2.11。這一版處理客戶的兩項建議。

---

## 一、改了什麼

| 客戶說 | 原因 | 處理 |
|---|---|---|
| 載入首頁時影片會 lag | 主視覺影片是 4K 原檔：電腦 285.8 MB、手機 257.7 MB，一開頁就開始下載；「Our Work」第 4 張卡（Construction Industry Council Test）也用同一支 285.8 MB 的影片；另外 3 張作品卡一開頁也同時播放 | 1. 主視覺影片壓成網頁版：電腦 1080p **9.0 MB**、手機 720p **4.5 MB**（畫面幾乎看不出差別），換到測試站<br>2. 主題：作品卡影片捲到畫面才載入、播放，離開畫面就暫停（首頁與 Work 頁） |
| 跑馬燈隨機抓 Client logo | — | 每次載入首頁，從所有 Client logos 隨機挑 **8 個**；Client logos 編輯畫面的「Homepage ticker」勾選拿掉（不需要了） |

提醒：Client logos 目前多是客戶牆用的正方形 logo，在 34px 高的跑馬燈裡會偏小；之後若換成橫式、去背的 logo 會更好看（客戶牆也會跟著換）。

## 二、安裝步驟（測試站，已由 Claude 完成）

1. 外觀 → 佈景主題 → 上傳 `guru-theme-v1.2.12-20261108.zip` → 以上傳的版本取代
2. 媒體庫上傳 `hero-desktop-1080p-web.mp4`、`hero-mobile-720p-web.mp4`
3. 外觀 → 自訂 → Guru site settings → Homepage：Hero video（desktop／mobile）換成上面兩支 → 發佈
4. Showcase →「Construction Industry Council Test」：Video 換成 `hero-desktop-1080p-web.mp4` → 更新

原始 4K 檔仍留在媒體庫，沒有刪除。壓縮版與原檔也存在 `~/Downloads/12_Snatch_Guru/影片壓縮/主視覺-20261108/`。

## 三、上傳後請確認

- [ ] 首頁打開不卡，主視覺影片正常播放、畫質清楚（電腦與手機）
- [ ] 往下捲到「Our Work」時作品卡影片才開始播放
- [ ] 重新整理首頁幾次，跑馬燈每次是不同的 8 個 logo
- [ ] Client logos 編輯畫面右側沒有「Homepage ticker」
