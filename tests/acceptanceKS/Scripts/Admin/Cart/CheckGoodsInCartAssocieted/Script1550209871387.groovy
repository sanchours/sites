/**
 * @description Проверка добавления товара со списка сопутствующих
 * @step Переходим в настройку товара
 * @step Переходим в список сопутствующих и добавляем товар
 * @step Переходим на лицевую часть сайта
 * @step Нажатие на кнопку "Купить" у первого товара в списке сопутствующих
 * @step Наживаем Перейти в корзину
 * @step Проверка названия первого товара
 */
import static com.kms.katalon.core.checkpoint.CheckpointFactory.findCheckpoint
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
import data.CatalogData as CatalogData
import com.kms.katalon.core.exception.StepErrorException as StepErrorException

CatalogData DataCatalog = new CatalogData()

WebUI.click(findTestObject('Object Repository/CMS/Cart/CreateCatalogSection/span_tab_catalog'))

WebUI.click(findTestObject('Object Repository/CMS/Cart/AddGoodInCart/img_edit_first_goods_list'))

WebUI.click(findTestObject('CMS/Goods/CreateAssocietedGoods/button_associeted_goods'))

WebUI.click(findTestObject('CMS/Goods/CreateAssocietedGoods/button_edit_associeted_goods'))

GoodsName = WebUI.getText(findTestObject('CMS/Goods/CreateNewGoodsInSet/div_name_goods_in_set'))

WebUI.click(findTestObject('CMS/Goods/CreateNewGoodsInSet/button_edit_goods'))

WebUI.verifyTextPresent(GoodsName,true )

WebUI.click(findTestObject('Object Repository/CMS/Cart/CreateCatalogSection/span_editor_catalog_tab'))

WebUI.delay(2)

WebUI.click(findTestObject('Object Repository/CMS/Cart/CreateCatalogSection/a_catalog_page_link'))

WebUI.switchToWindowIndex(1)

emptybasket = WebUI.getText(findTestObject('Object Repository/CMS/Cart/AddGoodInCart/ins_cart_clear'))

if (emptybasket != DataCatalog.emptybasket) {
	throw new StepErrorException('изначально корзина не пуста!')
}

WebUI.click(findTestObject('Object Repository/CMS/Cart/AddGoodInCart/a_first_title_goods_catalog_list'))

WebUI.click(findTestObject('Object Repository/CMS/Cart/AddGoodInCart/a_button_buy_associeted'))

WebUI.click(findTestObject('Object Repository/CMS/Cart/AddGoodInCart/button_road_to_cart'))

Title = WebUI.getText(findTestObject('Object Repository/CMS/Cart/AddGoodInCart/div_first_title_cart'))

if (Title != DataCatalog.itemnamesec) {
	throw new StepErrorException('Название товара неверено!')
}
