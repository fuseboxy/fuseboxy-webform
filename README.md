# FUSEBOXY-WEBFORM

````
<fusedoc>
	<description>
		Different ways to define [bean] config
		1. string : 'foo:123'
		2. array  : array('type' => 'foo', 'id' => 123)
		3. object : ORM::get('foo', 123)
	</description>
	<io>
		<in>
			<structure name="$webform" comments="config">
				<!-- essential config -->
				<mixed name="bean" />
				<string name="layoutPath" />
				<string_or_structure name="retainParam" optional="yes" comments="param to retain at button/form in order to maintain the record to edit (e.g. /my/webform/id=1)" format="query-string or associated-array" />
				<!-- permission -->
				<boolean name="allowEdit" optional="false" comments="user can view submitted form but cannot modify" />
				<boolean name="allowPrint" optional="false" comments="user can print submitted form" />
				<!-- steps of form -->
				<structure name="steps" optional="yes">
					<structure name="~stepName~">
						<list name="~fieldNameList~" value="~fieldWidthList~" optional="yes" delim="|" comments="use bootstrap grid layout for width">
							<list name="~fieldNameSubList~" delim="," comments="multiple fields in same column" />
						</list>
						<string name="~line~" optional="yes" comments="any amount of dash(-) or equal(=) signs" example="---" />
						<string name="~heading~" optional="yes" comments="begins with pound(#) sign(s); number of pound-signs stands for H1,H2,H3..." example="## General" />
						<string name="~output~" optional="yes" comments="begins with tide(~) sign" example="~<strong>output content directly</strong><br />" />
					</structure>
					<boolean name="confirm" optional="yes" default="true" />
				</structure>
				<!-- settings of each field used in form -->
				<structure name="fieldConfig">
					<structure name="~fieldName~">
						<string name="format" default="text" comments="text|textarea|checkbox|radio|date|file|image|signature|hidden|output|table|custom" />
						<string name="label" optional="yes" />
						<string name="inline-label" optional="yes" />
						<string name="placeholder" optional="yes" />
						<string name="icon" optional="yes" />
						<string name="help" optional="yes" comments="help text show below input field" />
						<boolean name="inline" optional="yes" comments="for [format=checkbox|radio]" />
						<!-- options -->
						<structure name="options" optional="yes" comments="show dropdown when specified">
							<string name="~optionValue~" value="~optionText~" optional="yes" />
							<structure name="~optGroup~" optional="yes">
								<structure name="~optionValue~" value="~optionText~" />
							</structure>
						</structure>
						<!-- attribute -->
						<boolean name="required" optional="yes" />
						<boolean name="readonly" optional="yes" comments="output does not pass value; readonly does" />
						<string name="default" optional="yes" comments="filling with this value if field has no value" />
						<string name="value" optional="yes" comments="force filling with this value even if field has value" />
						<string name="sameAs" optional="yes" value="~anotherFieldName~" comments="sync value from another field name" />
						<number name="maxlength" optional="yes" />
						<number name="minlength" optional="yes" />
						<string name="dataAllowed" optional="yes" />
						<string name="dataDisallowed" optional="yes" />
						<!-- styling -->
						<string name="class" optional="yes" comments="class applied to input field" />
						<string name="style" optional="yes" comments="style applied to input field" />
						<string name="wrapperClass" optional="yes" comments="class applied to webform-input" />
						<string name="wrapperStyle" optional="yes" comments="style applied to webform-input" />
						<!-- for [format=file|image] only -->
						<string name="filesize" optional="yes" comments="max file size in bytes" example="2MB|500KB" />
						<list name="filetype" optional="yes" delim="," example="pdf,doc,docx" />
						<string name="filesizeError" optional="yes" comments="error message shown when file size failed; use {FILE_SIZE} as mask" />
						<string name="filetypeError" optional="yes" comments="error message shown when file type failed; use {FILE_TYPE} as mask" />
						<!-- for [format=image] only -->
						<string name="resize" optional="yes" example="800x600|1024w|100h" />
						<!-- for [format=date] only -->
						<string name="datepickerFormat" optional="yes" example="Y-m-d|Y-m|.." />
						<string name="datepickerLocale" optional="yes" example="en|en-GB|zh|zh-TW|.." />
						<!-- for [format=table] only -->
						<string name="tableTitle" optional="yes" />
						<structure name="tableHeader" optional="yes">
							<string name="~columnHeader~" value="~columnWidth~" />
						</structure>
						<structure name="tableRow" optional="yes">
							<structure name="~rowFieldName~" />
						</structure>
						<boolean name="appendRow" optional="yes" />
						<boolean name="removeRow" optional="yes" />
						<structure name="scriptPath">
							<file name="tableHeader" optional="yes" default="~appPath~/view/webform/input.table.header.php" />
							<file name="tableRow" optional="yes" default="~appPath~/view/webform/input.table.row.php" />
						</structure>
						<!-- for [format=custom] only -->
						<file name="scriptPath" optional="yes" example="/path/to/custom/input.php" />
						<!-- advanced -->
						<structure name="toggleAttr" comments="toggle attribute of another field while modifying this field">
							<!-- target field -->
							<string name="target" comments="single field" />
							<array name="target" comments="multiple fields" />
							<list name="targetSelector" delim="," comments="use css-selector to define the fields" />
							<!-- things related to target field -->
							<structure name="field|element|wrapper|column" comments="{element} is alias of {field}">
								<structure name="when|whenNot">
									<structure name="~thisFieldValue~">
										<string_or_boolean name="~targetFieldAttrName~" value="~targetFieldAttrValue~" comments="use string to set attribute value; use {true} to add attribute without value; use {false|null} to remove attribute" />
									</structure>
								</structure>
							</structure>
						</structure>
						<structure name="toggleValue" comments="toggle value of another field while modifying this field">
							<!-- target field -->
							<string name="target" comments="single field" />
							<array name="target" comments="multiple fields" />
							<list name="targetSelector" delim="," comments="use css-selector to define the fields" />
							<!-- things related to target field -->
							<structure name="field|element|wrapper|column" comments="{element} is alias of {field}">
								<structure name="when|whenNot">
									<string name="~thisFieldValue~" value="~targetFieldValue~" />
								</structure>
							</structure>
						</structure>
						<structure name="toggleClass" comments="toggle class of another field while modifying this field">
							<!-- target field -->
							<string name="target" comments="single field" />
							<array name="target" comments="multiple fields" />
							<list name="targetSelector" delim="," comments="use css-selector to define the fields" />
							<!-- things related to target field -->
							<structure name="field|element|wrapper|column" comments="{element} is alias of {field}">
								<structure name="when|whenNot">
									<string name="~thisFieldValue~" value="~className~" />
								</structure>
							</structure>
						</structure>
					</structure>
				</structure>
				<!-- email notification settings -->
				<boolean_or_structure name="notification" optional="yes" default="false" comments="set to {false} to send no email">
					<string name="fromName" />
					<string name="from" />
					<list name="to" delim=";," />
					<list name="cc" delim=";," />
					<list name="bcc" delim=";," />
					<string name="subject" />
					<string name="body" />
				</boolean_or_structure>
				<!-- other settings -->
				<boolean_or_string name="writeLog" optional="yes" default="false" comments="simply true to log with default action; or specify action name to log" />
				<boolean_or_string name="snapshot" optional="yes" default="false" comments="simply true to save to {snapshot} table; or specify table name to save" />
				<boolean_or_string name="autosave" optional="yes" deafult="false" comments="simply true to save to {autosave} table; or specify table name to save" />
				<boolean name="opened" optional="yes" default="true" comments="whether the form is opened" />
				<boolean name="closed" optional="yes" default="false" comments="whether the form is closed" />
				<!-- customization -->
				<structure name="customMessage">
					<string name="opened" />
					<string name="closed" />
					<string name="completed" />
					<string name="neverSaved" comments="for autosave only" />
					<string name="lastSavedAt" comments="for autosave only" />
					<string name="lastSavedOn" comments="for autosave only" />
				</structure>
				<structure name="customButton">
					<structure name="next|back|edit|submit|update|print|autosave|chooseFile|chooseAnother">
						<string name="icon" />
						<string name="text" />
					</structure>
				</structure>
			</structure>
			<structure name="Webform::$libPath">
				<string name="uploadFile" />
				<string name="uploadFileProgress" />
			</structure>
			<structure name="config" scope="$fusebox" comments="for file upload">
				<string name="uploadDir" optional="yes" comments="server path for saving file" />
				<string name="uploadUrl" optional="yes" comments="web path for image source" />
			</structure>
		</in>
		<out />
	</io>
</fusedoc>
````