/**
 * @description Создание модификации товара
 * @step Переход в модуль "Каталог"
 * @step Переход в "Настройки"
 * @step Установка галочки "Выводить модификации"
 * @step Сохранение изменений
 * @step Переход в интерфейс каталога
 * @step Переход на детальную товара
 * @step Переход в "Модификации"
 * @step Нажатие на кнопку "Добавить"
 * @step Заполнение полей
 * @step Созрание модификации товара
 * @step Проверка наличия модификации в списке
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

WebUI.click(findTestObject('CMS/Goods/CreateNewModifications/div_catalog'))

//WebUI.click(findTestObject('CMS/Goods/CreateNewModifications/div_settings'))

WebUI.click(findTestObject('Object Repository/CMS/Goods/div_settings'))

WebUI.click(findTestObject('CMS/Goods/CreateNewModifications/checkbox_modifications'))

WebUI.click(findTestObject('CMS/Goods/CreateNewModifications/button_save_settings'))

WebUI.click(findTestObject('CMS/Goods/CreateNewModifications/div_goods'))

WebUI.doubleClick(findTestObject('CMS/Goods/CreateNewModifications/div_goods_detail'))

WebUI.click(findTestObject('CMS/Goods/CreateNewModifications/button_modifications'))

WebUI.click(findTestObject('CMS/Goods/CreateNewModifications/button_edit_modifications'))

WebUI.setText(findTestObject('CMS/Goods/CreateNewModifications/input_edit_adress'), 'test1')

WebUI.click(findTestObject('CMS/Goods/CreateNewModifications/save_modifications'))

WebUI.verifyElementText(findTestObject('CMS/Goods/CreateNewModifications/div_new_item_modifications'), 'test1')

