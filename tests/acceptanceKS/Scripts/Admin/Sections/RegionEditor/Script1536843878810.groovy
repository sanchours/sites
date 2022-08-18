/**
 * @description Редактор областей
 * @step Переход в раздел
 * @step Переход в редактор обласетей
 * @step Отключение модулей
 * @step Переход во вкладку "Редактор"
 * @step Переход на страницу
 * @step Проверка отсутствия отключённых модулей
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
import org.openqa.selenium.WebDriver as WebDriver
import org.openqa.selenium.WebElement as WebElement
import com.kms.katalon.core.webui.driver.DriverFactory as DriverFactory
import org.junit.After as After
import org.openqa.selenium.By as By
import org.openqa.selenium.By.ByClassName as ByClassName
import org.openqa.selenium.By.ByXPath as ByXPath

WebUI.click(findTestObject('CMS/Sections/RegionEditor/div_top_menu'))

WebUI.click(findTestObject('CMS/Sections/RegionEditor/div_catalog'))

WebUI.click(findTestObject('CMS/Sections/RegionEditor/div_tab_region_editor'))

WebUI.click(findTestObject('CMS/Sections/RegionEditor/div_off_path_line'))

WebUI.delay(3)

WebUI.mouseOver(findTestObject('CMS/Sections/RegionEditor/div_off_title'))

WebUI.click(findTestObject('CMS/Sections/RegionEditor/div_off_title'))

WebUI.delay(3)

WebUI.mouseOver(findTestObject('CMS/Sections/RegionEditor/div_off_section_list'))

WebUI.click(findTestObject('CMS/Sections/RegionEditor/div_off_section_list'))

WebUI.delay(3)

WebUI.click(findTestObject('CMS/Goods/CheckboxActivityGoods/div_editor'))

WebUI.click(findTestObject('CMS/Goods/CheckboxActivityGoods/div_catalog_link'))

WebUI.switchToWindowIndex(1)

WebUI.verifyElementNotPresent(findTestObject('CMS/Sections/RegionEditor/div_path_line_on_page'), 0)

WebUI.verifyElementNotPresent(findTestObject('CMS/Sections/RegionEditor/div_title_on_page'), 0)

WebUI.verifyElementNotPresent(findTestObject('CMS/Sections/RegionEditor/div_section_list_on_page'), 0)

