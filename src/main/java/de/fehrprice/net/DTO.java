package de.fehrprice.net;

import java.io.StringReader;
import java.nio.ByteBuffer;
import java.nio.charset.StandardCharsets;
import java.util.logging.Logger;

import javax.json.Json;
import javax.json.JsonObject;
import javax.json.JsonReader;

import de.fehrprice.crypto.Conv;

/**
 * Encapsulate all fields for data transfer and JSON import/export. 
 *
 */
public class DTO {

	private static Logger logger = Logger.getLogger(DTO.class.toString());

	public String command;
	public String id;
	public String key;
	public String signature;
	
	// examples:
	// InitClient / Alice / ffccee... / aabbcc44..
	
	public static String asJson(DTO dto)  {
		JsonObject json = Json.createObjectBuilder()
			     .add("command", dto.command)
			     .add("id", dto.id)
			     .add("key", dto.key)
			     .add("signature", dto.signature).build();
		String result = json.toString();
		return result;
	}

	public static DTO fromJsonString(String json)  {
		//logger.severe("DTO PARSING: " + json);
		JsonReader reader = Json.createReader(new StringReader(json));
		JsonObject jobj = reader.readObject();
		DTO dto = new DTO();
		dto.command = jobj.getString("command", null);
		dto.id = jobj.getString("id", null);
		dto.key = jobj.getString("key", null);
		dto.signature = jobj.getString("signature", null);
		return dto;
	}

	/**
	 * assemble message from parts command/id/key. Use this String to sign and verify.
	 */
	public byte[] getMessage() {
		String message = "";
		if (command != null) message = message + command;
		if (id != null) message = message + id;
		byte[] b = message.getBytes(StandardCharsets.UTF_8);
		
		if (key != null) {
			byte[] key_arr = Conv.toByteArray(this.key);
			b = ByteBuffer.allocate(b.length+key_arr.length).put(b).put(key_arr).array();
		}
		return b;
	}

	public boolean isInitClientCommand() {
		if (command != null && command.equals("InitClient")) {
			return true;
		}
		return false;
	}
}
