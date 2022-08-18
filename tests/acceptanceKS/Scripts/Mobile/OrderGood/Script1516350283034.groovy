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
import urlSite.BaseLink as BaseLink
import mobile.MobilePath as MobilePath

Mobile.startApplication(MobilePath.getPathFile(), true)

Mobile.setText(findTestObject('Mobile/android.widget.EditText0'), BaseLink.getUrlDef(), 0)

Mobile.tap(findTestObject('Mobile/android.widget.FrameLayout4'), 0)

not_run: Mobile.tap(findTestObject('Mobile/android.widget.btn_top'), 0)

Mobile.tap(findTestObject('Mobile/android.widget.addToCart'), 0)

Mobile.tap(findTestObject('Mobile/android.widget.Cart'), 0)

Mobile.tap(findTestObject('Mobile/android.widget.btn_checkout1'), 0)

Mobile.tap(findTestObject('Mobile/Order/android.widget.orderWithoutReg'), 0)

Mobile.setText(findTestObject('Mobile/Order/FormOrder/android.widget.name'), 'test', 0)

Mobile.setText(findTestObject('Mobile/Order/FormOrder/android.widget.phone'), '88888888', 0)

Mobile.setText(findTestObject('Mobile/Order/FormOrder/android.widget.email'), 'simakova@web-canape.com', 0)

Mobile.hideKeyboard()

Mobile.swipe(350, 900, 350, 200)

Mobile.setText(findTestObject('Mobile/Order/FormOrder/android.widget.address'), 'Smolensk, test 11', 0)

Mobile.hideKeyboard()

Mobile.setText(findTestObject('Mobile/Order/FormOrder/android.widget.comment'), 'small comment', 0)

Mobile.hideKeyboard()

Mobile.tap(findTestObject('Mobile/Order/FormOrder/android.widget.license'), 0)

Mobile.delay(2, FailureHandling.STOP_ON_FAILURE)

Mobile.tap(findTestObject('Mobile/Order/FormOrder/android.widget.btn_checkout'), 0)

Mobile.delay(5, FailureHandling.STOP_ON_FAILURE)

Mobile.tap(findTestObject('Mobile/Order/FormOrder/android.widget.ok'), 0)

Mobile.delay(2, FailureHandling.STOP_ON_FAILURE)

Mobile.closeApplication()

