from livekit.agents import function_tool
import enum
import logging
from db_driver import DatabaseDriver

logger = logging.getLogger("user-data")
logger.setLevel(logging.INFO)

DB = DatabaseDriver()

class CarDetails(enum.Enum):
    VIN = "vin"
    Make = "make"
    Model = "model"
    Year = "year"

class AssistantFnc:
    def __init__(self):
        self._car_details = {
            CarDetails.VIN: "",
            CarDetails.Make: "",
            CarDetails.Model: "",
            CarDetails.Year: ""
        }
    
    def get_car_str(self):
        car_str = ""
        for key, value in self._car_details.items():
            car_str += f"{key.value}: {value}\n"
        return car_str

    @function_tool
    async def lookup_car(self, vin: str):
        """Look up a car's details in the database using its VIN."""
        logger.info("lookup car - vin: %s", vin)
        result = DB.get_car_by_vin(vin)
        if result is None:
            return "Car not found."
        
        self._car_details = {
            CarDetails.VIN: result.vin,
            CarDetails.Make: result.make,
            CarDetails.Model: result.model,
            CarDetails.Year: result.year
        }
        return f"The car details are: {self.get_car_str()}"

    @function_tool
    async def get_car_details(self):
        """Get the details of the currently selected car."""
        if not self.has_car():
            return "No car has been looked up yet."
        return f"The car details are: {self.get_car_str()}"

    def has_car(self):
        return self._car_details[CarDetails.VIN] != ""