/**
 * @description Создание раздела для вывода баннеров
 * @step Открываем раздел верхнее меню
 * @step Жмем на кнопку добавления раздела
 * @step Заполняем поля названия, тех.названия и родительского раздела
 * @step Сохраняем раздел
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
import data.BannersData as BannersData

BannersData DataBanners = new BannersData()

WebUI.click(findTestObject('CMS/News/CreateNewNews/div_top_menu'))

WebUI.click(findTestObject('CMS/News/CreateNewNews/CreateNewSection/span_add_section'))

WebUI.setText(findTestObject('CMS/News/CreateNewNews/CreateNewSection/input_title_section'), DataBanners.sectionname)

WebUI.setText(findTestObject('CMS/Tree/NewSection/input_alias'), DataBanners.sectionalias)

WebUI.click(findTestObject('CMS/Tree/NewSection/change_parent_section'))

WebUI.click(findTestObject('CMS/Tree/NewSection/li_top_menu'))

WebUI.click(findTestObject('CMS/News/CreateNewNews/CreateNewSection/span_save_section'))

