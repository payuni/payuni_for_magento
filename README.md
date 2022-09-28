# PAYUNi for magento
 * 提供使用Magento購物車模組時，可直接透過安裝設定此套件，以便於快速串接統一金流PAYUNi之金流系統。

# 目錄
 * [版本需求](#版本需求)
 * [安裝方式](#安裝方式)
 * [相關設定](#相關設定)

# 版本需求
 <table>
   <tr>
     <th>Magento</th>
     <th>PHP</th>
   </tr>
   <tr>
     <td align="center">2.3.5 ↑</td>
     <td align="center">7.3 ↑</td>
   </tr>
 </table>

# 安裝方式
 * 將下載下來的壓縮檔複製PAYUNi資料夾放至magent根目錄/app/code/底下
 * 若code資料夾不存在請複製code資料夾放至magent根目錄/app/底下
 <br/><img src="https://github.com/payuni/sample_picture/blob/main/magento/magento_install.jpg" width="50%" height="50%"/><br/><br/>
 * 設定完以上設定後請執行更新指令
 * 更新指令
```
php bin/magento setup:upgrade
php bin/magento setup:static-content:deploy -f
```
# 相關設定
 * 購物車後台 → STORES → Configuration
 <br/><img src="https://github.com/payuni/sample_picture/blob/main/magento/magento_setting1.jpg" width="30%" height="30%"/><br/><br/>
 * SALES → Payment Methods
 <br/><img src="https://github.com/payuni/sample_picture/blob/main/magento/magento_setting2.jpg" width="30%" height="30%"/><br/><br/>
 * 展開 OTHER PAYMENT METHODS: → 展開 統一金流 PAYUNi
  * 金流設定
    * 請登入PAYUNi平台檢視商店串接資訊取得商店代號 、 Hash Key及 Hash IV。
    * 統一金流 商店代號 ： 填入PAYUNi平台商店的 商店代號
    * 統一金流 Hash Key： 填入PAYUNi平台商店的 Hash Key
    * 統一金流 Hash IV ： 填入PAYUNi平台商店的 IV Key
    * 測試模組 ： 是否開啟測試模組
 <br/><img src="https://github.com/payuni/sample_picture/blob/main/magento/magento_setting3.jpg" width="80%" height="80%"/><br/><br/>
