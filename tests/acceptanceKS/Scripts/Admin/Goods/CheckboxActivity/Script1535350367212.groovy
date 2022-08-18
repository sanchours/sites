/**
 * @description Галочка "Купить в 1 клик"
 * @step Переход в каталог
 * @step Переход в редактор карточек
 * @step Переход на детальную базовой карточки
 * @step Переход в редактирование поля "Купить в 1 клик"
 * @step Установка активности поля 
 * @step Сохранение изменений
 * @step Переход в список товаров
 * @step Установка галочки "Купить в 1 клик"
 * @step Переход на пользовательскую часть
 * @step Проверка наличия кнопки "Купить в 1 клик"
 * 
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
import urlSite.BaseLink as BaseLink

WebUI.click(findTestObject('CMS/Goods/CreateNewCard/div_catalog'))

WebUI.click(findTestObject('CMS/Goods/CreateNewCard/div_card_editor'))

WebUI.doubleClick(findTestObject('CMS/Goods/CheckboxActivity/div_base_cart_detail'))

WebUI.doubleClick(findTestObject('CMS/Goods/CheckboxActivity/div_base_cart_buy_one_click'))

WebUI.click(findTestObject('CMS/Goods/CheckboxActivity/checkbox_activity'))

WebUI.click(findTestObject('CMS/Goods/CheckboxActivity/button_save_field'))

WebUI.click(findTestObject('CMS/Goods/CreateNewModifications/div_goods'))

WebUI.click(findTestObject('CMS/Goods/CheckboxActivity/checkbox_buy_one_click_of_goods'))

WebUI.navigateToUrl(BaseLink.getUrlDef())

WebUI.verifyElementPresent(findTestObject('CMS/Goods/CheckboxActivity/a_buy_one_click'), 1)

