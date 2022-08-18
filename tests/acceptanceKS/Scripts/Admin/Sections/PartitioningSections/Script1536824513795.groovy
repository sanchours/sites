/**
 * @desceription Разводка разделов
 * @step Переход во вкладку "Список разделов"
 * @step Раскрытие списка разделов
 * @step Вывод раздела из списка в разводку
 * @step Сохранение
 * @step Переход во вкладку "Редактор"
 * @step Переход на страницу
 * @step Проверка наличия разводки
 */

import static com.kms.katalon.core.checkpoint.CheckpointFactory.findCheckpoint
import static com.kms.katalon.core.testcase.TestCaseFactory.findTestCase
import static com.kms.katalon.core.testdata.TestDataFactory.findTestData
import static com.kms.katalon.core.testobject.ObjectRepository.findTestObject
import com.kms.katalon.core.checkpoint.Checkpoint as Checkpoint
import com.kms.katalon.core.cucumber.keyword.CucumberBuiltinKeywords as CucumberKW
import com.kms.katalon.core.mobile.keyword.MobileBuiltInKeywords as Mobile
import com.kms.katalon.core.model.FailureHandling as FailureHandling
import com.kms.katalon.core.testcase.TestCase as TestCase
import com.kms.katalon.core.testdata.TestData as TestData
import com.kms.katalon.core.testobject.TestObject as TestObject
import com.kms.katalon.core.webservice.keyword.WSBuiltInKeywords as WS
import com.kms.katalon.core.webui.keyword.WebUiBuiltInKeywords as WebUI
import internal.GlobalVariable as GlobalVariable

WebUI.click(findTestObject('CMS/Sections/PartitioningSections/div_tab_section_list'))

WebUI.click(findTestObject('CMS/Sections/PartitioningSections/div_partitioning_sections'))

nameSection = WebUI.getText(findTestObject('CMS/Sections/PartitioningSections/div_partitioning_section_item'))

nameSection = nameSection.substring(1)

WebUI.click(findTestObject('CMS/Sections/PartitioningSections/div_partitioning_section_item'))

WebUI.click(findTestObject('CMS/Sections/PartitioningSections/button_save'))

WebUI.click(findTestObject('CMS/Sections/PartitioningSections/div_tab_editor'))

WebUI.click(findTestObject('CMS/Goods/CheckboxActivityGoods/div_catalog_link'))

WebUI.switchToWindowIndex(1)

WebUI.verifyElementText(findTestObject('CMS/Sections/PartitioningSections/div_section_on_page'), nameSection)

