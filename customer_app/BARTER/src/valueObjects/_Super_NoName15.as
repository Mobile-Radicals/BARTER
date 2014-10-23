/**
 * This is a generated class and is not intended for modification.  To customize behavior
 * of this value object you may modify the generated sub-class of this class - NoName15.as.
 */

package valueObjects
{
import com.adobe.fiber.services.IFiberManagingService;
import com.adobe.fiber.util.FiberUtils;
import com.adobe.fiber.valueobjects.IValueObject;
import flash.events.Event;
import flash.events.EventDispatcher;
import mx.binding.utils.ChangeWatcher;
import mx.collections.ArrayCollection;
import mx.events.CollectionEvent;
import mx.events.PropertyChangeEvent;
import mx.validators.ValidationResult;
import valueObjects.Data;
import valueObjects.Data1;

import flash.net.registerClassAlias;
import flash.net.getClassByAlias;
import com.adobe.fiber.core.model_internal;
import com.adobe.fiber.valueobjects.IPropertyIterator;
import com.adobe.fiber.valueobjects.AvailablePropertyIterator;

use namespace model_internal;

[ExcludeClass]
public class _Super_NoName15 extends flash.events.EventDispatcher implements com.adobe.fiber.valueobjects.IValueObject
{
    model_internal static function initRemoteClassAliasSingle(cz:Class) : void
    {
    }

    model_internal static function initRemoteClassAliasAllRelated() : void
    {
        valueObjects.Data1.initRemoteClassAliasSingleChild();
        valueObjects.Data.initRemoteClassAliasSingleChild();
    }

    model_internal var _dminternal_model : _NoName15EntityMetadata;
    model_internal var _changedObjects:mx.collections.ArrayCollection = new ArrayCollection();

    public function getChangedObjects() : Array
    {
        _changedObjects.addItemAt(this,0);
        return _changedObjects.source;
    }

    public function clearChangedObjects() : void
    {
        _changedObjects.removeAll();
    }

    /**
     * properties
     */
    private var _internal_message : Boolean;
    private var _internal_data1 : ArrayCollection;
    model_internal var _internal_data1_leaf:valueObjects.Data1;
    private var _internal_data : ArrayCollection;
    model_internal var _internal_data_leaf:valueObjects.Data;

    private static var emptyArray:Array = new Array();


    /**
     * derived property cache initialization
     */
    model_internal var _cacheInitialized_isValid:Boolean = false;

    model_internal var _changeWatcherArray:Array = new Array();

    public function _Super_NoName15()
    {
        _model = new _NoName15EntityMetadata(this);

        // Bind to own data or source properties for cache invalidation triggering
        model_internal::_changeWatcherArray.push(mx.binding.utils.ChangeWatcher.watch(this, "data1", model_internal::setterListenerData1));
        model_internal::_changeWatcherArray.push(mx.binding.utils.ChangeWatcher.watch(this, "data", model_internal::setterListenerData));

    }

    /**
     * data/source property getters
     */

    [Bindable(event="propertyChange")]
    public function get message() : Boolean
    {
        return _internal_message;
    }

    [Bindable(event="propertyChange")]
    public function get data1() : ArrayCollection
    {
        return _internal_data1;
    }

    [Bindable(event="propertyChange")]
    public function get data() : ArrayCollection
    {
        return _internal_data;
    }

    public function clearAssociations() : void
    {
    }

    /**
     * data/source property setters
     */

    public function set message(value:Boolean) : void
    {
        var oldValue:Boolean = _internal_message;
        if (oldValue !== value)
        {
            _internal_message = value;
            this.dispatchEvent(mx.events.PropertyChangeEvent.createUpdateEvent(this, "message", oldValue, _internal_message));
        }
    }

    public function set data1(value:*) : void
    {
        var oldValue:ArrayCollection = _internal_data1;
        if (oldValue !== value)
        {
            if (value is ArrayCollection)
            {
                _internal_data1 = value;
            }
            else if (value is Array)
            {
                _internal_data1 = new ArrayCollection(value);
            }
            else if (value == null)
            {
                _internal_data1 = null;
            }
            else
            {
                throw new Error("value of data1 must be a collection");
            }
            this.dispatchEvent(mx.events.PropertyChangeEvent.createUpdateEvent(this, "data1", oldValue, _internal_data1));
        }
    }

    public function set data(value:*) : void
    {
        var oldValue:ArrayCollection = _internal_data;
        if (oldValue !== value)
        {
            if (value is ArrayCollection)
            {
                _internal_data = value;
            }
            else if (value is Array)
            {
                _internal_data = new ArrayCollection(value);
            }
            else if (value == null)
            {
                _internal_data = null;
            }
            else
            {
                throw new Error("value of data must be a collection");
            }
            this.dispatchEvent(mx.events.PropertyChangeEvent.createUpdateEvent(this, "data", oldValue, _internal_data));
        }
    }

    /**
     * Data/source property setter listeners
     *
     * Each data property whose value affects other properties or the validity of the entity
     * needs to invalidate all previously calculated artifacts. These include:
     *  - any derived properties or constraints that reference the given data property.
     *  - any availability guards (variant expressions) that reference the given data property.
     *  - any style validations, message tokens or guards that reference the given data property.
     *  - the validity of the property (and the containing entity) if the given data property has a length restriction.
     *  - the validity of the property (and the containing entity) if the given data property is required.
     */

    model_internal function setterListenerData1(value:flash.events.Event):void
    {
        if (value is mx.events.PropertyChangeEvent)
        {
            if (mx.events.PropertyChangeEvent(value).newValue)
            {
                mx.events.PropertyChangeEvent(value).newValue.addEventListener(mx.events.CollectionEvent.COLLECTION_CHANGE, model_internal::setterListenerData1);
            }
        }
        _model.invalidateDependentOnData1();
    }

    model_internal function setterListenerData(value:flash.events.Event):void
    {
        if (value is mx.events.PropertyChangeEvent)
        {
            if (mx.events.PropertyChangeEvent(value).newValue)
            {
                mx.events.PropertyChangeEvent(value).newValue.addEventListener(mx.events.CollectionEvent.COLLECTION_CHANGE, model_internal::setterListenerData);
            }
        }
        _model.invalidateDependentOnData();
    }


    /**
     * valid related derived properties
     */
    model_internal var _isValid : Boolean;
    model_internal var _invalidConstraints:Array = new Array();
    model_internal var _validationFailureMessages:Array = new Array();

    /**
     * derived property calculators
     */

    /**
     * isValid calculator
     */
    model_internal function calculateIsValid():Boolean
    {
        var violatedConsts:Array = new Array();
        var validationFailureMessages:Array = new Array();

        var propertyValidity:Boolean = true;
        if (!_model.data1IsValid)
        {
            propertyValidity = false;
            com.adobe.fiber.util.FiberUtils.arrayAdd(validationFailureMessages, _model.model_internal::_data1ValidationFailureMessages);
        }
        if (!_model.dataIsValid)
        {
            propertyValidity = false;
            com.adobe.fiber.util.FiberUtils.arrayAdd(validationFailureMessages, _model.model_internal::_dataValidationFailureMessages);
        }

        model_internal::_cacheInitialized_isValid = true;
        model_internal::invalidConstraints_der = violatedConsts;
        model_internal::validationFailureMessages_der = validationFailureMessages;
        return violatedConsts.length == 0 && propertyValidity;
    }

    /**
     * derived property setters
     */

    model_internal function set isValid_der(value:Boolean) : void
    {
        var oldValue:Boolean = model_internal::_isValid;
        if (oldValue !== value)
        {
            model_internal::_isValid = value;
            _model.model_internal::fireChangeEvent("isValid", oldValue, model_internal::_isValid);
        }
    }

    /**
     * derived property getters
     */

    [Transient]
    [Bindable(event="propertyChange")]
    public function get _model() : _NoName15EntityMetadata
    {
        return model_internal::_dminternal_model;
    }

    public function set _model(value : _NoName15EntityMetadata) : void
    {
        var oldValue : _NoName15EntityMetadata = model_internal::_dminternal_model;
        if (oldValue !== value)
        {
            model_internal::_dminternal_model = value;
            this.dispatchEvent(mx.events.PropertyChangeEvent.createUpdateEvent(this, "_model", oldValue, model_internal::_dminternal_model));
        }
    }

    /**
     * methods
     */


    /**
     *  services
     */
    private var _managingService:com.adobe.fiber.services.IFiberManagingService;

    public function set managingService(managingService:com.adobe.fiber.services.IFiberManagingService):void
    {
        _managingService = managingService;
    }

    model_internal function set invalidConstraints_der(value:Array) : void
    {
        var oldValue:Array = model_internal::_invalidConstraints;
        // avoid firing the event when old and new value are different empty arrays
        if (oldValue !== value && (oldValue.length > 0 || value.length > 0))
        {
            model_internal::_invalidConstraints = value;
            _model.model_internal::fireChangeEvent("invalidConstraints", oldValue, model_internal::_invalidConstraints);
        }
    }

    model_internal function set validationFailureMessages_der(value:Array) : void
    {
        var oldValue:Array = model_internal::_validationFailureMessages;
        // avoid firing the event when old and new value are different empty arrays
        if (oldValue !== value && (oldValue.length > 0 || value.length > 0))
        {
            model_internal::_validationFailureMessages = value;
            _model.model_internal::fireChangeEvent("validationFailureMessages", oldValue, model_internal::_validationFailureMessages);
        }
    }

    model_internal var _doValidationCacheOfData1 : Array = null;
    model_internal var _doValidationLastValOfData1 : ArrayCollection;

    model_internal function _doValidationForData1(valueIn:Object):Array
    {
        var value : ArrayCollection = valueIn as ArrayCollection;

        if (model_internal::_doValidationCacheOfData1 != null && model_internal::_doValidationLastValOfData1 == value)
           return model_internal::_doValidationCacheOfData1 ;

        _model.model_internal::_data1IsValidCacheInitialized = true;
        var validationFailures:Array = new Array();
        var errorMessage:String;
        var failure:Boolean;

        var valRes:ValidationResult;
        if (_model.isData1Available && _internal_data1 == null)
        {
            validationFailures.push(new ValidationResult(true, "", "", "data1 is required"));
        }

        model_internal::_doValidationCacheOfData1 = validationFailures;
        model_internal::_doValidationLastValOfData1 = value;

        return validationFailures;
    }
    
    model_internal var _doValidationCacheOfData : Array = null;
    model_internal var _doValidationLastValOfData : ArrayCollection;

    model_internal function _doValidationForData(valueIn:Object):Array
    {
        var value : ArrayCollection = valueIn as ArrayCollection;

        if (model_internal::_doValidationCacheOfData != null && model_internal::_doValidationLastValOfData == value)
           return model_internal::_doValidationCacheOfData ;

        _model.model_internal::_dataIsValidCacheInitialized = true;
        var validationFailures:Array = new Array();
        var errorMessage:String;
        var failure:Boolean;

        var valRes:ValidationResult;
        if (_model.isDataAvailable && _internal_data == null)
        {
            validationFailures.push(new ValidationResult(true, "", "", "data is required"));
        }

        model_internal::_doValidationCacheOfData = validationFailures;
        model_internal::_doValidationLastValOfData = value;

        return validationFailures;
    }
    

}

}
