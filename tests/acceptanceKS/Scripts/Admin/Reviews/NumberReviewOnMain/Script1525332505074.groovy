 /**
 * @description Количество отзывов на главной
 * @step Переход в верхнее меню
 * @step Переход на главную
 * @step Переход в редактор областей
 * @step Вывод отзывов на главную
 * @step Переход в верхнее меню
 * @step Переход на главную
 * @step Переход в настройки параметров
 * @step Установка значения "Количество отзывов на странице"  = 2
 * @step Переход в раздел отзывы
 * @step Переход по ссылке на страницу отзывов на лицевой части
 * @step Проверка количества выведенных отзывов
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
import com.sun.org.apache.xalan.internal.xsltc.compiler.If as If
import com.sun.org.apache.xml.internal.dtm.ref.IncrementalSAXSource_Filter.StopException as StopException
import com.kms.katalon.core.webui.keyword.WebUiBuiltInKeywords as WebUI
import internal.GlobalVariable as GlobalVariable
import org.openqa.selenium.WebDriver as WebDriver
import org.openqa.selenium.WebElement as WebElement
import com.kms.katalon.core.webui.driver.DriverFactory as DriverFactory
import org.junit.After as After
import org.openqa.selenium.By as By
import org.openqa.selenium.By.ByClassName as ByClassName
import org.openqa.selenium.By.ByXPath as ByXPath

WebUI.click(findTestObject('CMS/Reviews/SectionReviewIdOnTheMain/div_on_the_main'))

WebUI.click(findTestObject('CMS/Reviews/div_top_menu'))

WebUI.click(findTestObject('CMS/Reviews/ReviewOnTheMain/div_home'))

WebUI.click(findTestObject('CMS/Reviews/ReviewOnTheMain/span_region_editor'))

WebUI.click(findTestObject('CMS/Reviews/ReviewOnTheMain/img_enable'))

WebUI.click(findTestObject('CMS/Reviews/SectionReviewIdOnTheMain/div_top_menu'))

WebUI.click(findTestObject('CMS/Reviews/SectionReviewIdOnTheMain/div_home'))

if (WebUI.verifyElementNotVisible(findTestObject('CMS/Reviews/SectionReviewIdOnTheMain/span_settings'), FailureHandling.OPTIONAL)) {
    for (int i = 0; i < 2; i++) {
        WebUI.click(findTestObject('CMS/Reviews/NumberReviewOnThe/div_chrom_scroll'))
    }
    
    WebUI.click(findTestObject('CMS/Reviews/SectionReviewIdOnTheMain/span_settings'))
} else {
    WebUI.click(findTestObject('CMS/Reviews/SectionReviewIdOnTheMain/span_settings'))
}

WebUI.click(findTestObject('CMS/Reviews/SectionReviewIdOnTheMain/img_detail_settings_review'))

WebUI.setText(findTestObject('CMS/Reviews/NumberOfReviewOnTheMain/input_number_of_review_on_the_main'), '1')

WebUI.click(findTestObject('CMS/Reviews/SectionReviewIdOnTheMain/button_save'))

WebUI.delay(3)

WebUI.click(findTestObject('CMS/Reviews/div_top_menu'))

WebUI.click(findTestObject('CMS/Reviews/SectionReviewIdOnTheMain/div_home'))

WebUI.click(findTestObject('Site/ReviewCheck/span_review_ediror_tab'))

WebUI.click(findTestObject('Site/ReviewCheck/a_review_page_link'))

WebUI.switchToWindowIndex(1)

WebDriver driver = DriverFactory.getWebDriver()

WebElement containerReviews = driver.findElement(By.className('b-guestbox'))

ArrayList<WebElement> Reviews = new ArrayList<WebElement>()

Reviews.addAll(containerReviews.findElements(By.xpath('//div[contains(@class,\'guestbox__item\')]')))

int countReviews = Reviews.size()

if (countReviews != 1) {
    return err //driver.close()
}

