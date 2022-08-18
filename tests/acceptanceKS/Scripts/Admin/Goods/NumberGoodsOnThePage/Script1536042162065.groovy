 /**
 * @description Количество товаров на странице
 * @step Переход в модуль "Каталог"
 * @step Переход в настройки вывода товарных позиций
 * @step Установка значения в поле "Количество товаров на странице"
 * @step Сохранение
 * @step Переход в каталожный раздел
 * @step Проверка количества товаров на странице
 */ import static com.kms.katalon.core.checkpoint.CheckpointFactory.findCheckpoint
import static com.kms.katalon.core.testcase.TestCaseFactory.findTestCase
import static com.kms.katalon.core.testdata.TestDataFactory.findTestData
import static com.kms.katalon.core.testobject.ObjectRepository.findTestObject
import com.kms.katalon.core.checkpoint.Checkpoint as Checkpoint
import com.kms.katalon.core.checkpoint.CheckpointFactory as CheckpointFactory
import com.kms.katalon.core.mobile.keyword.MobileBuiltInKeywords as MobileBuiltInKeywords
import com.kms.katalon.core.mobile.keyword.MobileBuiltInKeywords as Mobile
import com.kms.katalon.core.model.FailureHandling as FailureHandling
import com.kms.katalon.core.testcase.TestCase as TestCase
import com.kms.katalon.core.testcase.TestCaseFactory as TestCaseFactory
import com.kms.katalon.core.testdata.TestData as TestData
import com.kms.katalon.core.testdata.TestDataFactory as TestDataFactory
import com.kms.katalon.core.testobject.ObjectRepository as ObjectRepository
import com.kms.katalon.core.testobject.TestObject as TestObject
import com.kms.katalon.core.webservice.keyword.WSBuiltInKeywords as WSBuiltInKeywords
import com.kms.katalon.core.webservice.keyword.WSBuiltInKeywords as WS
import com.kms.katalon.core.webui.keyword.WebUiBuiltInKeywords as WebUiBuiltInKeywords
import com.kms.katalon.core.webui.keyword.WebUiBuiltInKeywords as WebUI
import internal.GlobalVariable as GlobalVariable
import org.openqa.selenium.WebDriver as WebDriver
import org.openqa.selenium.WebElement as WebElement
import com.kms.katalon.core.webui.driver.DriverFactory as DriverFactory
import org.junit.After as After
import org.openqa.selenium.By as By
import org.openqa.selenium.By.ByClassName as ByClassName
import org.openqa.selenium.By.ByXPath as ByXPath

numberGoods = 1

WebUI.click(findTestObject('CMS/Goods/span_catalog'))

WebUI.click(findTestObject('CMS/Goods/NumberGoodsOnThePage/div_settings_goods'))

WebUI.click(findTestObject('CMS/Goods/NumberGoodsOnThePage/input_number_goods_on_the_page'), FailureHandling.STOP_ON_FAILURE)

WebUI.click(findTestObject('CMS/Goods/NumberGoodsOnThePage/button_save'))

WebUI.click(findTestObject('CMS/Form/DeleteForm/span_ok'))

WebUI.click(findTestObject('CMS/Goods/NumberGoodsOnThePage/div_section'))

WebUI.click(findTestObject('CMS/Goods/CheckboxActivityGoods/div_top_menu'))

WebUI.click(findTestObject('CMS/Goods/CheckboxActivityGoods/div_group_goods'))

WebUI.click(findTestObject('CMS/Goods/CheckboxActivityGoods/div_section_catalog'))

WebUI.click(findTestObject('CMS/Goods/CheckboxActivityGoods/div_catalog_link'))

WebUI.switchToWindowIndex(1)

WebDriver driver = DriverFactory.getWebDriver()

WebElement containerGoods = driver.findElement(By.cssSelector(".b-catalogbox"))

ArrayList<WebElement> goodsItem = new ArrayList<WebElement>()

goodsItem.addAll(containerGoods.findElements(By.xpath('//div[contains(@class,\'catalogbox__item\')]')))

int countGoods = goodsItem.size()

if (countGoods != numberGoods) {
    return err //driver.close()
}

